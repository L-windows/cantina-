<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function openapi()
    {
      $serverUrl = url('/api/v1');

      $spec = [
        'openapi' => '3.0.1',
        'info' => [
          'title' => 'Cantina Pay API',
          'version' => 'v1',
          'description' => 'OpenAPI spec with basic schemas and security',
        ],
        'servers' => [[ 'url' => $serverUrl ]],
        'paths' => [
          '/auth/login' => [
            'post' => [
              'summary' => 'Login user (sanctum)',
              'requestBody' => ['content' => ['application/json' => ['schema' => ['type' => 'object','properties'=>['email'=>['type'=>'string'],'password'=>['type'=>'string']]]]]],
              'responses' => ['200' => ['description' => 'OK']],
            ],
          ],
          '/auth/register' => [ 'post' => ['summary' => 'Register user','requestBody'=>['content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/UserCreate']]]],'responses' => ['201'=>['description'=>'Created']]] ],
          '/products' => [
            'get' => ['summary' => 'List products','responses'=>['200'=>['description'=>'OK','content'=>['application/json'=>['schema'=>['type'=>'array','items'=>['$ref'=>'#/components/schemas/Product']]]]]]],
            'post' => ['summary' => 'Create product (auth)','requestBody'=>['content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/ProductCreate']]]],'responses'=>['201'=>['description'=>'Created']]],
          ],
          '/products/{id}' => [
            'get' => ['summary' => 'Get product','parameters'=>[['name'=>'id','in'=>'path','required'=>true,'schema'=>['type'=>'integer']]],'responses'=>['200'=>['description'=>'OK','content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/Product']]]]]],
            'put' => ['summary'=>'Update product (auth)','parameters'=>[['name'=>'id','in'=>'path','required'=>true,'schema'=>['type'=>'integer']]],'requestBody'=>['content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/ProductUpdate']]]],'responses'=>['200'=>['description'=>'OK']]],
            'delete' => ['summary'=>'Delete product (auth)','parameters'=>[['name'=>'id','in'=>'path','required'=>true,'schema'=>['type'=>'integer']]],'responses'=>['204'=>['description'=>'No Content']]],
          ],
          '/categories' => [ 'get' => ['summary'=>'List categories','responses'=>['200'=>['description'=>'OK']]], 'post' => ['summary'=>'Create category','requestBody'=>['content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/CategoryCreate']]]],'responses'=>['201'=>['description'=>'Created']]] ],
          '/wallet' => [ 'get' => ['summary'=>'Get current user wallet','responses'=>['200'=>['description'=>'OK','content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/Wallet']]]]]],
          '/wallet/topup' => ['post'=>['summary'=>'Topup wallet','requestBody'=>['content'=>['application/json'=>['schema'=>['type'=>'object','properties'=>['wallet_id'=>['type'=>'integer'],'amount'=>['type'=>'number']]]]]],'responses'=>['200'=>['description'=>'OK']]]],
          '/transactions' => [ 'get' => ['summary'=>'List transactions','responses'=>['200'=>['description'=>'OK']]], 'post' => ['summary'=>'Create transaction','requestBody'=>['content'=>['application/json'=>['schema'=>['$ref'=>'#/components/schemas/TransactionCreate']]]],'responses'=>['201'=>['description'=>'Created']]] ],
          '/pos/purchase' => [ 'post' => ['summary'=>'POS purchase','requestBody'=>['content'=>['application/json'=>['schema'=>['type'=>'object','properties'=>['qr_code'=>['type'=>'string'],'product_ids'=>['type'=>'array','items'=>['type'=>'integer']]]]]]],'responses'=>['201'=>['description'=>'Created']]] ],
          '/admin/users' => [ 'get' => ['summary'=>'Admin list users','responses'=>['200'=>['description'=>'OK']]] ],
        ],
        'components' => [
          'securitySchemes' => [ 'bearerAuth' => ['type'=>'http','scheme'=>'bearer','bearerFormat'=>'JWT'] ],
          'schemas' => [
            'Product' => [ 'type'=>'object','properties'=>['id'=>['type'=>'integer'],'name'=>['type'=>'string'],'slug'=>['type'=>'string'],'price'=>['type'=>'number'],'description'=>['type'=>'string'],'category_id'=>['type'=>'integer'],'is_active'=>['type'=>'boolean']] ],
            'ProductCreate' => ['type'=>'object','required'=>['name','price','category_id'],'properties'=>['name'=>['type'=>'string'],'price'=>['type'=>'number'],'category_id'=>['type'=>'integer'],'description'=>['type'=>'string']]],
            'ProductUpdate' => ['type'=>'object','properties'=>['name'=>['type'=>'string'],'price'=>['type'=>'number'],'description'=>['type'=>'string'],'is_active'=>['type'=>'boolean']]],
            'Category' => ['type'=>'object','properties'=>['id'=>['type'=>'integer'],'name'=>['type'=>'string'],'slug'=>['type'=>'string'],'description'=>['type'=>'string']]],
            'CategoryCreate' => ['type'=>'object','required'=>['name'],'properties'=>['name'=>['type'=>'string'],'description'=>['type'=>'string']]],
            'Wallet' => ['type'=>'object','properties'=>['id'=>['type'=>'integer'],'student_id'=>['type'=>'integer'],'balance'=>['type'=>'number']]],
            'Transaction' => ['type'=>'object','properties'=>['id'=>['type'=>'integer'],'wallet_id'=>['type'=>'integer'],'user_id'=>['type'=>'integer'],'amount'=>['type'=>'number'],'type'=>['type'=>'string'],'description'=>['type'=>'string'],'created_at'=>['type'=>'string','format'=>'date-time']]],
            'TransactionCreate' => ['type'=>'object','required'=>['wallet_id','amount','type'],'properties'=>['wallet_id'=>['type'=>'integer'],'amount'=>['type'=>'number'],'type'=>['type'=>'string'],'description'=>['type'=>'string']]],
            'User' => ['type'=>'object','properties'=>['id'=>['type'=>'integer'],'name'=>['type'=>'string'],'email'=>['type'=>'string'],'role'=>['type'=>'string']]],
            'UserCreate' => ['type'=>'object','required'=>['name','email','password'],'properties'=>['name'=>['type'=>'string'],'email'=>['type'=>'string'],'password'=>['type'=>'string'],'role'=>['type'=>'string']]],
          ],
        ],
        'security' => [ [ 'bearerAuth' => [] ] ],
      ];

        return response()->json($spec);
    }

    public function docs()
    {
        $openapiUrl = url('/api/v1/openapi.json');
        $html = <<<HTML
<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Cantina Pay API Docs</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css" />
  </head>
  <body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
    <script>
      window.onload = function() {
        const ui = SwaggerUIBundle({
          url: '$openapiUrl',
          dom_id: '#swagger-ui',
        });
      };
    </script>
  </body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
