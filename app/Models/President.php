<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class President extends Model
{
    protected $fillable = [
        'name',
        'gender',
        'party',
        'age_group',
        'background',
        'home_region',
        'ideology',
        'support_strength',
    ];

    public function getStartingStats(): array
    {
        $baseApproval = 52;
        $baseStability = 50;
        $basePartySupport = $this->getBasePartySupport();

        return [
            'approval' => $baseApproval,
            'stability' => $baseStability,
            'party_support' => $basePartySupport,
        ];
    }

    protected function getBasePartySupport(): int
    {
        return match($this->support_strength) {
            'landslide' => 70,
            'comfortable' => 60,
            'razor_thin' => 50,
            'electoral_weakness' => 45,
        };
    }

    public function getVoterModifiers(): array
    {
        $isDemocrat = $this->party === 'democrat';
        
        $modifiers = [
            'students' => $isDemocrat ? 15 : -15,
            'yuppie' => $isDemocrat ? 5 : 5,
            'young_conservatives' => $isDemocrat ? -20 : 20,
            'working_class' => $isDemocrat ? 10 : -5,
            'suburban' => $isDemocrat ? 8 : 5,
            'rural' => $isDemocrat ? -15 : 15,
            'small_business' => 0,
            'corporate' => 0,
            'public_sector' => 0,
            'retirees' => 0,
            'minorities' => $isDemocrat ? 20 : -20,
            'independents' => 0,
        ];

        // Ideology adjustments
        if ($this->ideology === 'hardcore') {
            $modifiers['students'] += $isDemocrat ? 5 : -5;
            $modifiers['rural'] += $isDemocrat ? -5 : 5;
            $modifiers['minorities'] += $isDemocrat ? 5 : -5;
        } elseif ($this->ideology === 'swing') {
            $modifiers['independents'] = 15;
            $modifiers['suburban'] += 10;
            $modifiers['students'] = $isDemocrat ? 5 : -5;
            $modifiers['rural'] = $isDemocrat ? -5 : 5;
        }

        // Home region adjustments
        switch ($this->home_region) {
            case 'latino':
                $modifiers['minorities'] += 15;
                $modifiers['rural'] += $isDemocrat ? -5 : 5;
                break;
            case 'urban':
                $modifiers['students'] += 10;
                $modifiers['minorities'] += 10;
                $modifiers['yuppie'] += 5;
                break;
            case 'rural':
                $modifiers['rural'] += 15;
                $modifiers['minorities'] += $isDemocrat ? 5 : -5;
                break;
            case 'southern':
                $modifiers['rural'] += 10;
                $modifiers['minorities'] += $isDemocrat ? 5 : -10;
                break;
            case 'midwest':
                $modifiers['working_class'] += 10;
                $modifiers['suburban'] += 5;
                break;
            case 'west_coast':
                $modifiers['students'] += 10;
                $modifiers['minorities'] += 5;
                break;
            case 'east_coast':
                $modifiers['yuppie'] += 10;
                $modifiers['minorities'] += 5;
                break;
        }

        // Background adjustments
        switch ($this->background) {
            case 'military':
                $modifiers['rural'] += $isDemocrat ? -5 : 10;
                $modifiers['retirees'] += $isDemocrat ? 5 : 10;
                break;
            case 'business':
                $modifiers['yuppie'] += 10;
                $modifiers['corporate'] += 15;
                $modifiers['working_class'] += $isDemocrat ? -10 : 5;
                break;
            case 'law':
                $modifiers['retirees'] += 5;
                $modifiers['minorities'] += $isDemocrat ? 5 : -5;
                break;
            case 'governor':
                $modifiers['suburban'] += 10;
                $modifiers['independents'] += 10;
                $modifiers['rural'] += $isDemocrat ? 0 : 5;
                break;
            case 'senator':
                $modifiers['party_support'] = 15;
                $modifiers['minorities'] += $isDemocrat ? 5 : -5;
                break;
            case 'congress':
                $modifiers['party_support'] = 10;
                $modifiers['working_class'] += 5;
                break;
            case 'outsider':
                $modifiers['independents'] += 20;
                $modifiers['party_support'] = -15;
                break;
        }

        return $modifiers;
    }

    public function getDescription(): string
    {
        $gender = $this->gender === 'male' ? 'He' : ($this->gender === 'female' ? 'She' : 'They');
        $party = ucfirst($this->party);
        $ideology = $this->ideology === 'hardcore' ? 'hardline' : ($this->ideology === 'swing' ? 'centrist' : 'traditional');
        
        return "{$this->name} is a {$ideology} {$party} president.";
    }
}
