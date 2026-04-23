import requests
import time
import random

URL = "http://127.0.0.1:8000/api/scc/data"

vbat = 11.5
soc = 30

while True:
    # simulasi panel
    vpv = random.uniform(17, 19)
    ipv = random.uniform(0.5, 1.2)

    # fuzzy sederhana
    error = 14.4 - vbat
    delta_error = random.uniform(-0.5, 0.5)

    duty_cycle = max(0, min(100, error * 10))

    # update baterai
    vbat += duty_cycle * 0.001
    soc += 0.1

    if soc > 100:
        soc = 100

    # fase charging
    if vbat < 13:
        fase = "Bulk"
    elif vbat < 14.4:
        fase = "Absorption"
    else:
        fase = "Float"

    # label fuzzy (dummy dulu)
    label_e = "PS"
    label_de = "ZO"

    data = {
        "vpv": vpv,
        "ipv": ipv,
        "vbat": vbat,
        "ibat": ipv,
        "soc": soc,
        "duty_cycle": duty_cycle,
        "fase": fase,
        "label_e": label_e,
        "label_de": label_de
    }

    try:
        r = requests.post(URL, json=data)
        print("Status:", r.status_code)
        print("Response:", r.text)
    except Exception as e:
        print("Error:", e)

    time.sleep(2)



