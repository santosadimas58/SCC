import random
import os
import time

import requests

# =========================================================
# configuration
# =========================================================
URL = "http://127.0.0.1:8088/api/scc/data"
DEMO_FAST = True

BULK_TARGET = 14.4
ABSORPTION_TARGET = 14.4
FLOAT_TARGET = 13.6
FUZZY_OUTPUTS = {
    "NB": 5.0,
    "NS": 22.0,
    "ZO": 45.0,
    "PS": 70.0,
    "PB": 92.0,
}
FUZZY_RULES = {
    "NB": {"NB": "NB", "NS": "NB", "ZO": "NB", "PS": "NS", "PB": "ZO"},
    "NS": {"NB": "NB", "NS": "NS", "ZO": "NS", "PS": "ZO", "PB": "PS"},
    "ZO": {"NB": "NS", "NS": "NS", "ZO": "ZO", "PS": "PS", "PB": "PS"},
    "PS": {"NB": "NS", "NS": "ZO", "ZO": "PS", "PS": "PS", "PB": "PB"},
    "PB": {"NB": "ZO", "NS": "PS", "ZO": "PB", "PS": "PB", "PB": "PB"},
}

DAY_LENGTH_STEPS = 18 if DEMO_FAST else 60
SLEEP_SECONDS = 1 if DEMO_FAST else 2

vbat = 11.9
soc = 28.0
prev_error = BULK_TARGET - vbat
sim_step = 0
pv_trend = 18.2


def env_value(key, default=""):
    if key in os.environ:
        return os.environ[key]

    try:
        with open(".env", encoding="utf-8") as env_file:
            for line in env_file:
                line = line.strip()
                if not line or line.startswith("#") or "=" not in line:
                    continue

                name, value = line.split("=", 1)
                if name == key:
                    return value.strip().strip('"').strip("'")
    except FileNotFoundError:
        pass

    return default


API_TOKEN = env_value("SCC_API_TOKEN", "local-scc-demo-token")


def clamp(value, minimum, maximum):
    return max(minimum, min(maximum, value))


# =========================================================
# fuzzy control
# =========================================================
def fuzzy_error_label(value):
    if value <= -0.60:
        return "NB"
    if value <= -0.20:
        return "NS"
    if value < 0.20:
        return "ZO"
    if value < 0.60:
        return "PS"
    return "PB"


def fuzzy_delta_label(value):
    if value <= -0.20:
        return "NB"
    if value <= -0.05:
        return "NS"
    if value < 0.05:
        return "ZO"
    if value < 0.20:
        return "PS"
    return "PB"


def charging_phase(vpv, vbat, soc):
    if vpv < 15.0 or vpv < vbat + 1.0:
        return "Standby"
    if soc >= 95.0 or vbat >= 14.35:
        return "Float"
    if soc >= 80.0 or vbat >= 14.10:
        return "Absorption"
    return "Bulk"


def target_for_phase(fase, vbat):
    if fase == "Bulk":
        return BULK_TARGET
    if fase == "Absorption":
        return ABSORPTION_TARGET
    if fase == "Float":
        return FLOAT_TARGET
    return vbat


def fuzzy_duty(fase, output_label, vpv, vbat):
    if fase == "Standby":
        return 0.0

    duty = FUZZY_OUTPUTS[output_label]

    if fase == "Absorption":
        duty *= 0.72
    elif fase == "Float":
        duty *= 0.38

    if vpv > 0:
        duty = min(duty, (vbat / vpv * 100.0) + 8.0)

    return clamp(duty, 0.0, 96.0)


while True:
    # =====================================================
    # day/night simulation
    # =====================================================
    cycle_pos = sim_step % (DAY_LENGTH_STEPS * 2)
    is_day = cycle_pos < DAY_LENGTH_STEPS

    if is_day:
        pv_trend += random.uniform(-0.35, 0.35)
        pv_trend = clamp(pv_trend, 17.4, 19.8)
        vpv = clamp(pv_trend + random.uniform(-0.25, 0.25), 17.0, 20.0)
    else:
        pv_trend += random.uniform(-0.1, 0.1)
        pv_trend = clamp(pv_trend, 0.0, 0.8)
        vpv = clamp(pv_trend + random.uniform(-0.08, 0.08), 0.0, 0.8)

    # =====================================================
    # phase logic
    # =====================================================
    fase = charging_phase(vpv, vbat, soc)
    target_voltage = target_for_phase(fase, vbat)

    error = target_voltage - vbat
    delta_error = error - prev_error
    label_e = fuzzy_error_label(error)
    label_de = fuzzy_delta_label(delta_error)
    output_label = FUZZY_RULES[label_e][label_de]
    duty_cycle = fuzzy_duty(fase, output_label, vpv, vbat)

    if fase != "Standby":
        max_ipv = 2.8 + max(0.0, vpv - 17.0) * 0.9
        ipv = clamp((duty_cycle / 100.0) * max_ipv + random.uniform(-0.2, 0.2), 0.2, 6.0)
    else:
        ipv = 0.0

    # =====================================================
    # battery update
    # =====================================================
    if fase != "Standby":
        if fase == "Bulk":
            vbat += 0.09 + (duty_cycle / 1600.0) + random.uniform(-0.015, 0.018)
            soc += 0.85 + (ipv * 0.06) + random.uniform(-0.05, 0.08)
            vbat = min(vbat, 14.24)
        elif fase == "Absorption":
            vbat += 0.022 + (duty_cycle / 4500.0) + random.uniform(-0.01, 0.01)
            soc += 0.28 + (ipv * 0.025) + random.uniform(-0.03, 0.05)
            vbat = min(vbat, 14.45)
        else:
            vbat += (FLOAT_TARGET - vbat) * 0.28 + random.uniform(-0.02, 0.02)
            soc += 0.05 + (ipv * 0.005) + random.uniform(-0.01, 0.015)
            vbat = clamp(vbat, 13.45, 13.8)
    else:
        vbat += (12.25 - vbat) * 0.05 + random.uniform(-0.015, 0.015)
        soc -= random.uniform(0.01, 0.05)
        vbat = clamp(vbat, 11.8, 13.7)

    soc = clamp(soc, 0, 100)
    ibat = clamp(ipv * (0.92 + random.uniform(-0.04, 0.04)), 0.0, 6.0)

    # =====================================================
    # API send
    # =====================================================
    data = {
        "vpv": round(vpv, 2),
        "ipv": round(ipv, 2),
        "vbat": round(vbat, 2),
        "ibat": round(ibat, 2),
        "soc": round(soc, 2),
    }

    try:
        headers = {"X-SCC-Token": API_TOKEN} if API_TOKEN else {}
        response = requests.post(URL, json=data, headers=headers, timeout=5)
        period = "Day" if is_day else "Night"
        print(f"[{period}] {response.status_code} | {data}")
    except Exception as exc:
        print("Error:", exc)

    prev_error = error
    sim_step += 1
    time.sleep(SLEEP_SECONDS)
