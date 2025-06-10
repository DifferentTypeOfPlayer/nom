<?php
require_once(__DIR__.'/crest.php');

// Кастомная сущность
function createCitizenshipApplicationEntity()
{
    $entityId = 'CITIZENSHIP_APPLICATION';
    $entityFields = [
        'TITLE' => [
            'type' => 'string',
            'title' => 'Application Title'
        ],
        'CLIENT_NAME' => [
            'type' => 'string',
            'title' => 'Client Name'
        ],
        'FAMILY_MEMBERS' => [
            'type' => 'string',
            'title' => 'Family Members (spouse, children)'
        ],
        'PROGRAM_NAME' => [
            'type' => 'string',
            'title' => 'Program Name'
        ],
        'TOTAL_INVESTMENT' => [
            'type' => 'double',
            'title' => 'Total Investment'
        ],
        'APPLICANT_CONTRIBUTION' => [
            'type' => 'double',
            'title' => 'Applicant Contribution'
        ],
        'SPOUSE_CONTRIBUTION' => [
            'type' => 'double',
            'title' => 'Spouse Contribution'
        ],
      
    ];

    $result = CRest::call('entity.add', [
        'ENTITY' => $entityId,
        'NAME' => 'Citizenship Applications',
        'ACCESS' => [
            'READ' => 'WRITE',
            'ADD' => 'WRITE',
            'UPDATE' => 'WRITE',
            'DELETE' => 'WRITE'
        ],
        'FIELDS' => $entityFields
    ]);

    return $result;
}

// Шаблон док-а
function createDocumentTemplate()
{
    $templateFile = __DIR__.'';
    if (!file_exists($templateFile)) {
        throw new Exception("Template file not found");
    }

    $templateData = [
        'NAME' => 'Nomad Freedom Commercial Offer',
        'ENTITY_TYPE_ID' => 'CITIZENSHIP_APPLICATION',
        'TEMPLATE' => base64_encode(file_get_contents($templateFile)),
        'FIELDS' => [
            'Title' => ['TITLE' => 'Document Title'],
            'ClientName' => ['TITLE' => 'Client Name'],
            'FamilyMembers' => ['TITLE' => 'Family Members'],
            'ProgramName' => ['TITLE' => 'Program Name'],
            'TotalInvestment' => ['TITLE' => 'Total Investment'],
            'Date' => ['TITLE' => 'Document Date'],
            'ConsultantName' => ['TITLE' => 'Consultant Name'],
            'ConsultantPhone' => ['TITLE' => 'Consultant Phone'],
            'ConsultantEmail' => ['TITLE' => 'Consultant Email'],
           
        ]
    ];

    $result = CRest::call('documentgenerator.template.add', $templateData);
    return $result['result']['ID']; 
}

// Класс для генерации документов
class Generator
{
    public function generateDocument($entityId, $templateId)
    {
       
        $applicationData = CRest::call('entity.item.get', [
            'ENTITY' => 'CITIZENSHIP_APPLICATION',
            'ID' => $entityId
        ]);

        if (isset($applicationData['error'])) {
            throw new Exception("Failed to get application data: " . $applicationData['error_description']);
        }

        $data = $applicationData['result'];

        // формируем данные
        $documentData = [
            'Title' => 'Nomad Freedom Commercial Offer for ' . $data['CLIENT_NAME'],
            'Description' => 'Financial Estimate and Cost Breakdown for ' . $data['PROGRAM_NAME'],
            
            // Информация о клиенте
            'ClientName' => $data['CLIENT_NAME'],
            'FamilyMembers' => $data['FAMILY_MEMBERS'],
            'ProgramName' => $data['PROGRAM_NAME'],
            'TotalInvestment' => '$' . number_format($data['TOTAL_INVESTMENT'], 2),
            'Date' => date('jS F Y'),
            
            // Информация о консультанте
            'ConsultantName' => 'Susanna Uzakova',
            'ConsultantPhone' => '+7 (499) 100 21 29',
            'ConsultantEmail' => 'Susanna.u@nomadfreedom.com',
            
            // Таблица 
            'CostBreakdown' => [
                [
                    'Category' => 'Non-refundable contribution',
                    'Applicant' => '$' . number_format($data['APPLICANT_CONTRIBUTION'], 2),
                    'Spouse' => '$' . number_format($data['SPOUSE_CONTRIBUTION'], 2),
                    'Children' => '$0',
                    'Parents' => '-',
                    'Total' => '$' . number_format($data['APPLICANT_CONTRIBUTION'] + $data['SPOUSE_CONTRIBUTION'], 2)
                ],
                
            ],
            
            // График 
            'ProcessTimeline' => [
                [
                    'Timeline' => 'Month 1-2',
                    'ProcessSteps' => 'Initial paperwork and due diligence',
                    'Payments' => '$' . number_format($data['APPLICANT_CONTRIBUTION'] * 0.5, 2)
                ],
                
            ]
        ];

        // Генерация документа
        $document = CRest::call('documentgenerator.document.add', [
            'TEMPLATE_ID' => $templateId,
            'VALUE' => $documentData,
            'FIELDS' => [
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
            ]
        ]);

        if (isset($document['error'])) {
            throw new Exception("Document generation failed: " . $document['error_description']);
        }

        return [
            'url' => $document['result']['URL'],
            'documentId' => $document['result']['ID']
        ];
    }
}

// 11
try {
    // Инициализация 
    // $entityResult = createCitizenshipApplicationEntity();
    // $templateId = createDocumentTemplate();
    
    
    $generator = new Generator();
    $result = $generator->generateDocument(123, 123); 
    
    echo "Document generated: " . $result['url'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
