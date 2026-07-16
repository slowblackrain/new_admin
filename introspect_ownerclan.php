<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = new App\Services\Affiliate\OwnerclanService();

$reflection = new ReflectionClass($service);
$method = $reflection->getMethod('authenticate');
$method->setAccessible(true);
$token = $method->invoke($service);

$query = '
  query IntrospectionQuery {
    __schema {
      queryType { name }
      mutationType { name }
      types {
        ...FullType
      }
    }
  }

  fragment FullType on __Type {
    kind
    name
    description
    fields(includeDeprecated: true) {
      name
      description
      args {
        ...InputValue
      }
      type {
        ...TypeRef
      }
      isDeprecated
      deprecationReason
    }
    inputFields {
      ...InputValue
    }
    interfaces {
      ...TypeRef
    }
    enumValues(includeDeprecated: true) {
      name
      description
      isDeprecated
      deprecationReason
    }
    possibleTypes {
      ...TypeRef
    }
  }

  fragment InputValue on __InputValue {
    name
    description
    type { ...TypeRef }
    defaultValue
  }

  fragment TypeRef on __Type {
    kind
    name
    ofType {
      kind
      name
      ofType {
        kind
        name
        ofType {
          kind
          name
          ofType {
            kind
            name
            ofType {
              kind
              name
              ofType {
                kind
                name
                ofType {
                  kind
                  name
                }
              }
            }
          }
        }
      }
    }
  }
';

$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => 'Bearer ' . $token,
    'Content-Type' => 'application/json',
])->withoutVerifying()->post('https://api.ownerclan.com/v1/graphql', [
    'query' => $query
]);

file_put_contents('ownerclan_schema.json', $response->body());
echo "Schema saved to ownerclan_schema.json\n";
