<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'timeZone' => 'Europe/Berlin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'defaultRoute' => 'v1/default',
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module'
        ]
    ],
    'bootstrap' => [
        'log',
        //'common\components\EventBootstrap',
    ],
    'components' => [


        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['api'],
                    'logFile' => '@app/runtime/logs/api.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                // API AUTH
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/user/auth',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                // API RESET
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/user/reset',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'POST ask-reset' => 'ask-reset',
                        'POST reset' => 'reset',
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                // API USER
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/user',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                        'PUT {id}' => 'update-user',
                        'POST create' => 'create',
                        'POST {id}/persons' => 'create-person',
                        'POST {id}/change-password' => 'change-password',
                        'PUT reset-password' => 'reset-password',
                        'GET profile' => 'profile',
                        'GET drop-down-items' => 'drop-down-items',
                        'DELETE {id}/delete' => 'delete',
                        'POST <idUser>/organization/<idOrganization>' => 'add-organization'
                    ],
                ],
                // API ROLE
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/auth-item',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                        'GET roles' => 'roles',
                        'GET roles-language/<language>' => 'roles-by-language'
                    ],
                ],
                // API LOCATIONS
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/locations/city',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/locations/province',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/locations/region',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/locations/nation',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/locations/continent',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                    ],
                ],
                // API PERSONS
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/persons',
                    'except' => ['patch', 'head'],
                    'extraPatterns' => [
                        'OPTIONS <url:.*>' => 'options',
                        'GET {id}' => 'person',
                        'POST' => 'create',
                        'PUT {id}' => 'update',
                        'DELETE <id:\d+>' => 'delete',
                        'GET <idperson:\d+>/addresses' => 'list-addresses',
                        'POST <idperson:\d+>/addresses' => 'add-address',
                        'PUT addresses/<id:\d+>' => 'update-address',
                        'DELETE addresses/<id:\d+>' => 'delete-address',
                        'GET <idperson:\d+>/contacts' => 'list-contacts',
                        'POST <idperson:\d+>/contacts' => 'add-contact',
                        'PUT contacts/<id:\d+>' => 'update-contact',
                        'DELETE contacts/<id:\d+>' => 'delete-contact',
                        'GET <idperson:\d+>/diary' => 'list-diary',
                        'GET <idperson:\d+>/diary/<id:\d+>' => 'get-diary',
                        'POST <idperson:\d+>/diary' => 'create-diary',
                        'PUT <idperson:\d+>/diary/<id:\d+>' => 'update-diary',
                        'GET <idperson:\d+>/planning' => 'list-planning',
                        'POST <idperson:\d+>/planning' => 'create-planning',
                    ],
                ],
            ],
        ],
        'request' => [
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]

        ],
        'jwt' => [
            'class' => 'sizeg\jwt\Jwt',
            'key' => $params['secret-key'],
        ],
        //'response' => [
        //'class' => \yii\web\Response::className(),
        //'on beforeSend' => function ($event) {
        //$response = $event->sender;
        //if ($response->data !== null && ($exception = Yii::$app->getErrorHandler()->exception) !== null) {
        //$response->data = [
        //'error' => $response->data,
        //];
        //}
        //},
        //],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => yii\web\Response::FORMAT_JSON,
/*             'formatters' => [
               yii\web\Response::FORMAT_JSON => [
                 'class' => 'yii\web\JsonResponseFormatter',
                 'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                 'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
               ],
             ],*/
            'on beforeSend' => function ($event) {

                $prevent_log_sensible_information = "/\/user\/user\/change-password|auth\/auth\/login|auth\/reset\/reset/i";
                $params_a = "";

                if (!preg_match($prevent_log_sensible_information, Yii::$app->request->url)) :
                    if (Yii::$app->request->isPut) : $params_a = Yii::$app->request->bodyParams; endif;
                    if (Yii::$app->request->isPost) : $params_a = Yii::$app->request->post(); endif;
                else:
                    $params_a = "hidden call";
                endif;

                Yii::trace("[" . Yii::$app->request->method . "] " . Yii::$app->request->url . " " . json_encode($params_a) . " " . json_encode(Yii::$app->request->headers), "api call");

                $headers = Yii::$app->response->headers;

                $headers->add('Access-Control-Allow-Origin', '*');
                $headers->add('Access-Control-Allow-Headers', 'Authorization');
                $headers->add('Access-Control-Allow-Headers', 'Content-Type');
                $headers->add('Access-Control-Allow-Headers', 'Range');
                //$headers->add('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT', 'DELETE');

                $headers->add('Access-Control-Allow-Methods', 'POST');
                $headers->add('Access-Control-Allow-Methods', 'PUT');
                $headers->add('Access-Control-Allow-Methods', 'GET');
                $headers->add('Access-Control-Allow-Methods', 'DELETE');
                $headers->add('Access-Control-Allow-Methods', 'OPTIONS');

                $headers->add('Access-Control-Request-Method', 'POST');
                $headers->add('Access-Control-Request-Method', 'PUT');
                $headers->add('Access-Control-Request-Method', 'GET');
                $headers->add('Access-Control-Request-Method', 'DELETE');
                $headers->add('Access-Control-Request-Method', 'OPTIONS');

                $headers->add('Access-Control-Request-Headers', 'X-Wsse');

                $headers->add('Access-Control-Allow-Credentials', true);
                $headers->add('Access-Control-Max-Age', 3600);
                $headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
                $headers->add('Access-Control-Expose-Headers', 'X-Pagination-Page-Count');
                $headers->add('Access-Control-Expose-Headers', 'X-Pagination-Current-Page');
                $headers->add('Access-Control-Expose-Headers', 'X-Pagination-Per-Page');
                $headers->add('Access-Control-Expose-Headers', 'Accept-Ranges');
                $headers->add('Access-Control-Expose-Headers', 'Content-Encoding');
                $headers->add('Access-Control-Expose-Headers', 'Content-Length');
                $headers->add('Access-Control-Expose-Headers', 'Content-Range');

                $headers->remove('Location');

                $response = $event->sender;

                if ($response->data !== null) {
                    $response->data = [
                        'status' => Yii::$app->response->statusCode,
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = Yii::$app->response->statusCode;
                }
            },
        ],
    ],
    'params' => $params,
];


