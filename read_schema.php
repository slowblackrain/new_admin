<?php
$schemaStr = file_get_contents('C:\dometopia\new_admin\ownerclan_schema.json');
$schema = json_decode($schemaStr, true);

function findType($schema, $typeName) {
    if (isset($schema['data']['__schema']['types'])) {
        foreach ($schema['data']['__schema']['types'] as $type) {
            if ($type['name'] === $typeName) {
                return $type;
            }
        }
    }
    return null;
}

$metadataInput = findType($schema, 'ItemMetadataInput');
if ($metadataInput) {
    print_r($metadataInput);
} else {
    echo "ItemMetadataInput not found\n";
}
