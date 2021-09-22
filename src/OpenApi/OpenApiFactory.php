<?php

namespace App\OpenApi;


use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;

class OpenApiFactory implements OpenApiFactoryInterface
{

    private  $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {

        // Example how to add custom path
        //$openApi->getPaths()->addPath('/ping', new PathItem(null, 'Ping', null, new Operation('ping-id', [], [], 'Répond')));

        $openApi = $this->decorated->__invoke($context);

        // use this snippet to solve problem in get all tags when get item tag is disabled
        /** @var PathItem $path */
        foreach ($openApi->getPaths()->getPaths() as $key => $path) {
            if ($path->getGet() && $path->getGet()->getSummary() === 'hidden') {
                $openApi->getPaths()->addPath($key, $path->withGet(null));
            }
        }

        // get schema from security component
        $schemas = $openApi->getComponents()->getSecuritySchemes();

        // bearerAuth schema
        $schemas['bearerAuth'] = new \ArrayObject([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT'
        ]);

        // create custom schemas to use them in login endpoint
        $schemas = $openApi->getComponents()->getSchemas();

        // custom schema for credentials
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'example' => 'admin@gmail.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'admin'
                ]
            ]
        ]);

        // custom schema for refresh token
        $schemas['Refresh'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'refresh_token' => [
                    'type' => 'string',
                    'example' => '958d5a03c8112843a475d204abf5bea90722fc51325e0ccc57443e34b76e7013d2618ac7bda08efb59bbfc37e70f34cd6ab46ac00284d48935f6b860541325db',
                ]
            ]
        ]);

        // custom schema for token
        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ]
        ]);

        // clean parameters array
        $meOperation = $openApi->getPaths()->getPath('/api/myaccount')->getGet()->withParameters([]);
        $mePathItem = $openApi->getPaths()->getPath('/api/myaccount')->withGet($meOperation);
        $openApi->getPaths()->addPath('/api/myaccount', $mePathItem);


        // add path item for login action
        $pathItem = new PathItem(
            'Login endpoint',
            'Login endpoint summary',
            'Login endpoint description',
            null,
            null,
            // Your custom post operation
            new Operation(
                'postApiLogin',
                ['Auth'],
                [
                    // response specifications
                    '200' => [
                        'description' => 'Token JWT',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token'
                                ]
                            ]
                        ]
                    ],
                ],
                'Login endpoint summary',
                'Login endpoint description',
                null,
                [],
                new RequestBody(
                    'Login request body description',
                    new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                ),
            )
        );
        $openApi->getPaths()->addPath('/api/login', $pathItem);



        // add path item for logout action
        $pathItem = new PathItem(
            'Logout endpoint',
            'Logout endpoint summary',
            'Logout endpoint description',
            null,
            null,
            // Your custom post operation            
            new Operation(
                'postApiLogout',
                ['Auth'],
                [
                    '204' => []
                ],
                'Logout endpoint summary',
                'Logout endpoint description',
                null,
                [],
                null,
                null,
                false,
                [
                    [
                        "bearerAuth" => [
                            'type' => 'http',
                            'scheme' => 'bearer',
                            'bearerFormat' => 'JWT'
                        ]
                    ]
                ],
                null,
                []
            ),

        );
        $openApi->getPaths()->addPath('/logout', $pathItem);


        // add path item for refresh token action
        $pathItem = new PathItem(
            'Refresh Token endpoint',
            'Refresh Token endpoint summary',
            'Refresh Token endpoint description',
            null,
            null,
            // Your custom post operation            
            new Operation(
                'postApiRefreshToken',
                ['Auth'],
                [
                    '204' => []
                ],
                'Refresh Token endpoint summary',
                'Refresh Token endpoint description',
                null,
                [],
                new RequestBody(
                    'Refresh Token request body description',
                    new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Refresh'
                            ]
                        ]
                    ])
                ),
                null,
                false,
                [],
                null,
                []
            )
        );
        $openApi->getPaths()->addPath('/api/token/refresh', $pathItem);




        return $openApi;
    }
}
