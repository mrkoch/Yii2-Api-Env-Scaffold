<?php
return [
    'adminEmail' => 'info-covid@aslroma5.it',

    // Turni di lavoro
    'shifts' => [
      'M'=> ['name'=>'Mattina',   'start'=>'07:00',   'end'=>'13:30'],
      'P'=> ['name'=>'Pomeriggio','start'=>'13:30',   'end'=>'20:00'],
      'N'=> ['name'=>'Notte',     'start'=>'20:00',   'end'=>'07:00']
    ],
    'roleTraslateIt'=>[
        'SuperAdmin' => 'Super Amministratore',
        'Admin' => 'Amministratore',
        'Director' => 'Direttore',
        'HealtDirector' => 'Direttore Sanitario',
        'DistrictDirector' => 'Direttore Distretto',
        'DistrictOperator' => 'Operatori Comitato Distretto',
        'Administrative' => 'Amministrativi',
        'Primary' => 'Primario',
        'Doctor' => 'Medico di reparto',
        'DoctorCovid' => 'Medico Covid',
        'Headnurse' => 'Caposala/Capoinfermiere di reparto',
        'Nurse' => 'Infermiere di reparto',
        'NurseCovid' => 'Infermiere Covid',
        'NurseHomeVisit' => 'Infermiere visita domiciliare',
        'Slot' => 'Utente Slot',
        'Biologist' => 'Biologo',
        'Supervisor' => 'Account di visualizzazione',
        'Tdp' => 'Tecnico della prevenzione',
        'Operator_cc' => 'Operatore Call Center',
        'Operator_ps' => 'Operatore Ospedali / Pronto Soccorso',
    ],
    'roleTraslateEn'=>[
        'Super Amministratore' => 'SuperAdmin',
        'Amministratore' => 'Admin',
        'Direttore' => 'Director',
        'Direttore Sanitario' => 'HealtDirector',
        'Direttore Distretto' => 'DistrictDirector',
        'Operatori Comitato Distretto' => 'DistrictOperator',
        'Amministrativi' => 'Administrative',
        'Primario' => 'Primary',
        'Medico di reparto' => 'Doctor',
        'Medico Covid' => 'DoctorCovid',
        'Caposala/Capoinfermiere di reparto' => 'Headnurse',
        'Infermiere di reparto' => 'Nurse',
        'Infermiere Covid' => 'NurseCovid',
        'Infermiere visita domiciliare' => 'NurseHomeVisit',
        'Utente Slot' => 'Slot',
        'Biologo' => 'Biologist',
        'Account di visualizzazione' => 'Supervisor',
        'Tecnico della prevenzione' => 'Tdp',
        'Operatore Call Center' => 'Operator_cc',
        'Operatore Ospedali / Pronto Soccorso' => 'Operator_ps',
    ]

];
