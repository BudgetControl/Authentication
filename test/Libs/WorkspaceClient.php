<?php
namespace Budgetcontrol\Test\Libs;

use Budgetcontrol\Connector\Service\ConnectorInterface;
use Budgetcontrol\Connector\Model\Response as ModelResponse;

class WorkspaceClient implements ConnectorInterface {

     /**
     * Sets the payload for the connector.
     *
     * @param array $payload The payload data to be set.
     * @return self Returns the instance of the connector.
     */
    public function setPayload(array $payload): self
    {
        return $this;
    }

    /**
     * Sets the header for the connector.
     *
     * @param array $header The header to set.
     * @return self
     */
    public function setHeader(array $header): self
    {
        return $this;
    }

    /**
     * Sets the HTTP method for the connector.
     *
     * @param string $method The HTTP method to set.
     * @return self Returns the instance of the connector.
     */
    public function setMethod(string $method): self
    {
        return $this;
    }

    /**
     * Makes a call to the specified path and returns the HTTP response.
     *
     * @param string $path The path to call.
     * @return ModelResponse The HTTP response.
     */
    public function call(string $path, int $userId): ModelResponse
    {
        return new ModelResponse(
            201,
            json_encode([
                'workspace' => [
                    'name' => 'test',
                    'description' => 'test',
                    'current' => 1,
                    'user_id' => 2,
                    'uuid' => '4373a9a3-a482-4d5a-b8fe-c0572be7efe3',
                ]
            ])
        );
    }

}