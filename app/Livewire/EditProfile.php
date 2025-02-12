<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class EditProfile extends PersonalInfo
{
    public array $only = ['name', 'email', 'phone', 'bio'];

    protected function getProfileFormSchema(): array
    {
        $groupFields = Forms\Components\Group::make([
            $this->getNameComponent(),
            $this->getEmailComponent(),
            PhoneInput::make('phone')
                ->defaultCountry('bd')
                ->inputNumberFormat(PhoneInputNumberType::E164)
                ->displayNumberFormat(PhoneInputNumberType::E164)
                ->focusNumberFormat(PhoneInputNumberType::E164)
                ->disallowDropdown()
                ->autoPlaceholder('polite')
                ->initialCountry('bd'),
        ])->columnSpan(2);

        return [
            $this->hasAvatars ? filament('filament-breezy')->getAvatarUploadComponent() : null,
            $groupFields,
            Textarea::make('bio')
                ->columnSpanFull(),
        ];
    }

    public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
        if ($this->user->phone !== $data['phone']) {
            $data['phone_verified_at'] = null;
        }
        $this->user->update($data);
        $this->sendNotification();
    }
}
