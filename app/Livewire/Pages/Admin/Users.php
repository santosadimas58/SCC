<?php
namespace App\Livewire\Pages\Admin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
#[Layout('layouts.app')]
class Users extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $selectedRole = '';

    public $search = '';
    public $filterRole = '';

    public function resetFilter()
    {
        $this->search = '';
        $this->filterRole = '';
    }

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->selectedRole = '';
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email' . ($this->editMode ? ',' . $this->userId : ''),
            'selectedRole' => 'required',
        ];
        if (!$this->editMode) {
            $rules['password'] = 'required|min:6';
        }
        $this->validate($rules);

        if ($this->editMode) {
            $user = User::find($this->userId);
            $user->update(['name' => $this->name, 'email' => $this->email]);
            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'User berhasil diupdate.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'User berhasil ditambahkan.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        }

        $user->syncRoles([$this->selectedRole]);
        $this->closeModal();
    }

    public function edit($id)
    {
        $user = User::find($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRole = $user->getRoleNames()->first();
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        if ($id === auth()->id()) {
            $this->dispatch('mary-toast', toast: ['type' => 'warning', 'title' => 'Peringatan!', 'description' => 'Tidak bisa menghapus akun sendiri.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-warning', 'timeout' => 3000, 'noProgress' => false]);
            return;
        }
        User::find($id)->delete();
        $this->dispatch('mary-toast', toast: ['type' => 'error', 'title' => 'Dihapus!', 'description' => 'User berhasil dihapus.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-error', 'timeout' => 3000, 'noProgress' => false]);
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%'))
            ->when($this->filterRole, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $this->filterRole)))
            ->latest()
            ->get();

        $roles = Role::all();
        $roleOptions = $roles->map(fn($r) => ['id' => $r->name, 'name' => ucfirst($r->name)])->toArray();

        return view('livewire.pages.admin.users', compact('users', 'roles', 'roleOptions'));
    }
}
