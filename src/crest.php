<?php
require_once(__DIR__.'/crest.php');

class DocumentGenerator
{
    public function generateAndAttachToDeal($entityId, $templateId, $dealId)
    {
        // 1. Генерация документа
        $document = $this->generateDocument($entityId, $templateId);
        
        // 2. Прикрепление к сделке
        $this->attachToDeal($document['url'], $dealId);
        
        return [
            'documentUrl' => $document['url'],
            'dealUrl' => 'https://logasoft.bitrix24.ru/crm/deal/details/'.$dealId.'/'
        ];
    }

    private function generateDocument($entityId, $templateId)
    {
        $applicationData = CRest::call('entity.item.get', [
            'ENTITY' => 'CITIZENSHIP_APPLICATION',
            'ID' => $entityId
        ]);

        if (isset($applicationData['error'])) {
            throw new Exception("Ошибка получения данных: " . $applicationData['error_description']);
        }

        $data = $applicationData['result'];

        // данные документа
        $documentData = [
            'Title' => 'Коммерческое предложение Nomad Freedom для ' . $data['CLIENT_NAME'],
            'Description' => 'Финансовая смета для программы ' . $data['PROGRAM_NAME'],
            'ClientName' => $data['CLIENT_NAME'],
            'FamilyMembers' => $data['FAMILY_MEMBERS'],
            'ProgramName' => $data['PROGRAM_NAME'],
            'TotalInvestment' => '$' . number_format($data['TOTAL_INVESTMENT'], 2),
            'Date' => date('d.m.Y'),
            'ConsultantName' => 'Susanna Uzakova',
            'ConsultantPhone' => '+7 (499) 100 21 29',
            'ConsultantEmail' => 'Susanna.u@nomadfreedom.com',
            'CostBreakdown' => [
                [
                    'Category' => 'Безвозвратный взнос',
                    'Applicant' => '$' . number_format($data['APPLICANT_CONTRIBUTION'], 2),
                    'Spouse' => '$' . number_format($data['SPOUSE_CONTRIBUTION'], 2),
                    'Children' => '$0',
                    'Parents' => '-',
                    'Total' => '$' . number_format($data['APPLICANT_CONTRIBUTION'] + $data['SPOUSE_CONTRIBUTION'], 2)
                ]
            ],
            'ProcessTimeline' => [
                [
                    'Timeline' => '1-2 месяц',
                    'ProcessSteps' => 'Первичная проверка и подготовка документов',
                    'Payments' => '$' . number_format($data['APPLICANT_CONTRIBUTION'] * 0.5, 2)
                ]
            ]
        ];

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
            throw new Exception("Ошибка генерации документа: " . $document['error_description']);
        }

        return [
            'id' => $document['result']['ID'],
            'url' => $document['result']['URL']
        ];
    }

    private function attachToDeal($documentUrl, $dealId)
    {
        // Скачиваем документ
        $fileContent = file_get_contents($documentUrl);
        if ($fileContent === false) {
            throw new Exception("Не удалось загрузить документ по URL");
        }

        //Загружаем в Битрикс24
        $file = CRest::call('disk.folder.uploadfile', [
            'id' => 0, 
            'data' => [
                'NAME' => 'Коммерческое предложение Nomad Freedom.pdf',
                'fileContent' => base64_encode($fileContent)
            ]
        ]);

        if (isset($file['error'])) {
            throw new Exception("Ошибка загрузки файла: " . $file['error_description']);
        }

        //Прикрепляем к сделке
        $result = CRest::call('crm.deal.update', [
            'ID' => $dealId,
            'FIELDS' => [
                'UF_CRM_DOCUMENTS' => $file['result']['ID'] 
            ]
        ]);

        if (isset($result['error'])) {
            throw new Exception("Ошибка прикрепления к сделке: " . $result['error_description']);
        }

        return true;
    }
}


try {
    $generator = new DocumentGenerator();
    
    
    
    $result = $generator->generateAndAttachToDeal(123, 456, 9828);
    
    echo "Документ успешно создан и прикреплен к сделке!<br>";
    echo "<a href='".$result['documentUrl']."' target='_blank'>Открыть документ</a><br>";
    echo "<a href='".$result['dealUrl']."' target='_blank'>Перейти к сделке</a>";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
