{
  "request": {
    "type": "{{ $requestType }}",
    "subjectType": "{{ $requestSubjectType }}",
    "targetFields": [
      {{ json_encode($targetFields) }}
    ],
    "startDate": "{{ $startDate }}",
    "endDate": "{{ $endDate }}"
  },
  "package": {
    "type": "{{ $packageType }}",
    "identifier": "{{ $packageIdentifier }}",
    "title": "{{ $packageTitle }}",
    "description": "{{ $packageDescription }}",
    "contacts": [
        {{ json_encode($contacts) }}
    ],
    "keywords": "{{ $packageKeywords }}",
    "updatedAt": "{{ $packageUpdatedAt }}",
    "acknowledgements": "{{ $packageAcknowledgements }}",
    "geographicScope": "{{ $geographicScope }}",
    "taxonomicScope": "{{ $taxonomicScope }}",
    "temporalScope": "{{ $temporalScope }}",
    "languageSkills": "{{ $languageSkills }}",
    "targetFields": [
      {{ json_encode($targetFields) }}
    ],
    "dataSet": {
      "identifier": "{{ $dataSetIdentifier }}",
      "dataUrl": "{{ $dataSetUrl }}"
    },
    "parent": {
      "type": "{{ $parentType }}",
      "identifier": "{{ $parentIdentifier }}",
      "title": "{{ $parentTitle }}",
      "provider": "{{ $parentProvider }}",
      "description": "{{ $parentDescription }}",
      "url": "{{ $parentUrl }}",
      "contacts": [
        {{ json_encode($contacts) }}
      ]
    },
    "ppsr": {{ json_encode($ppsrFields) }}
  },
  "comment": "{{ $manifestComment }}"
}

