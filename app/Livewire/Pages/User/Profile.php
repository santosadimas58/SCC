<?php
namespace App\Livewire\Pages\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
#[Layout('layouts.app')]
class Profile extends Component
{
    public $name = '';
    public $email = '';
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        $this->name  = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name'  => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        auth()->user()->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('mary-toast', toast: [
            'type' => 'success', 'title' => 'Berhasil!',
            'description' => 'Profil berhasil diupdate.',
            'position' => 'toast-top toast-end', 'icon' => '',
            'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false
        ]);
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Password saat ini tidak sesuai.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->dispatch('mary-toast', toast: [
            'type' => 'success', 'title' => 'Berhasil!',
            'description' => 'Password berhasil diubah.',
            'position' => 'toast-top toast-end', 'icon' => '',
            'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false
        ]);
    }

    public function render()
    {
        return view('livewire.pages.user.profile');
    }
}
