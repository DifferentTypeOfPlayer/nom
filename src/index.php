<?php
require_once (__DIR__.'/crest.php');

$result = CRest::call('profile');
function addData(
    $method,
    $data
) {
   return CRest1::call(
        $method,
        $data
    );
}



$data = [
    'templateId' => $templateId,
    'entityTypeId' => $entityTypeId,
    'entityId' => $entityid, 
    'values' => [
        'Title' => 'Nomad Freedom Commercial Offer',
        'Description' => 'Financial Estimate and Cost Breakdown for Antigua and Barbuda citizenship by investment',
        'Picture' => null,
        
        // Клиент
        'ClientName' => '',
        'FamilyMembers' => '',
        'ProgramName' => '',
        'TotalInvestment' => '',
        'Date' => '',
        
        // Информация
        'ConsultantName' => 'Susanna Uzakova',
        'ConsultantPhone' => '+7 (499) 100 21 29',
        'ConsultantEmail' => 'Susanna.u@nomadfreedom.com',
        
        // Таблица
        'CostBreakdown' => [
            [
                'Category' => 'Non-refundable contribution',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '$',
                'Parents' => '',
                'Total' => ''
            ],
            [
                'Category' => 'Due Diligence',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '',
                'Parents' => '',
                'Total' => ''
            ],
            [
                'Category' => 'Bank commission for Due Diligence payment',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '',
                'Parents' => '',
                'Total' => ''
            ],
            [
                'Category' => 'Bank commission for Investment payment (7%)',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '',
                'Parents' => '',
                'Total' => ''
            ],
            [
                'Category' => 'Certificate of Naturalization',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '',
                'Parents' => '',
                'Total' => ''
            ],
            [
                'Category' => 'Nomad Freedom Legal Services',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '',
                'Parents' => '',
                'Total' => ''
            ],
            [
                'Category' => 'Total',
                'Applicant' => '',
                'Spouse' => '',
                'Children' => '',
                'Parents' => '',
                'Total' => ''
            ]
        ],
        
       
	],
    
    'fields' => [
        'CostBreakdown' => [
            'PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\ArrayDataProvider',
            'OPTIONS' => [
                'ITEM_NAME' => 'Row',
                'ITEM_PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\HashDataProvider',
            ],
        ],
        'ProcessTimeline' => [
            'PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\ArrayDataProvider',
            'OPTIONS' => [
                'ITEM_NAME' => 'TimelineItem',
                'ITEM_PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\HashDataProvider',
            ],
        ],
        // Define other fields as needed
    ]
];

echo '<pre>';
	print_r($result);
echo '</pre>';
