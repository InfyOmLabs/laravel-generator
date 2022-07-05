@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiTests }};

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use {{ $config->namespaces->tests }}\TestCase;
use {{ $config->namespaces->tests }}\ApiTestTrait;
use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};

class {{ $config->modelNames->name }}ApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_{{ $config->modelNames->snake }}()
    {
        ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}', ${{ $config->modelNames->camel }}
        );

        $this->assertApiResponse(${{ $config->modelNames->camel }});
    }

    /**
     * @test
     */
    public function test_read_{{ $config->modelNames->snake }}()
    {
        ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();

        $this->response = $this->json(
            'GET',
            '/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/'.${{ $config->modelNames->camel }}->{{ $config->primaryName }}
        );

        $this->assertApiResponse(${{ $config->modelNames->camel }}->toArray());
    }

    /**
     * @test
     */
    public function test_update_{{ $config->modelNames->snake }}()
    {
        ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();
        $edited{{ $config->modelNames->name }} = {{ $config->modelNames->name }}::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/'.${{ $config->modelNames->camel }}->{{ $config->primaryName }},
            $edited{{ $config->modelNames->name }}
        );

        $this->assertApiResponse($edited{{ $config->modelNames->name }});
    }

    /**
     * @test
     */
    public function test_delete_{{ $config->modelNames->snake }}()
    {
        ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/'.${{ $config->modelNames->camel }}->{{ $config->primaryName }}
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/'.${{ $config->modelNames->camel }}->{{ $config->primaryName }}
        );

        $this->response->assertStatus(404);
    }
}
