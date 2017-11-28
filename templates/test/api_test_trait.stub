<?php

trait ApiTestTrait
{
    private $response;
    public function assertApiResponse(Array $actualData)
    {
        $this->assertApiSuccess();

        $api_response = json_decode($this->response->getContent(), true);
        $responseData = $api_response['data'];

        $this->assertNotEmpty($responseData['id']);
        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess()
    {
        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(Array $actualData, Array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}