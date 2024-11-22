<?php
declare(strict_types=1);

namespace Budgetcontrol\Authentication\Domain\Model;

class WorkspaceSettings {
    private $uuid;
    private $data;
    private $name;

    public function __construct(string $uuid, string $data, string $name) {
        $this->uuid = $uuid;
        $this->data = $data;
        $this->name = $name;
    }

    public function getUuid(): string {
        return $this->uuid;
    }

    public function getData(): object {
        //check if is a valid json
        if(json_decode($this->data) === null) {
            throw new \Exception('Invalid json');
        }

        return json_decode($this->data, true);
    }

    public function getName(): string {
        return $this->name;
    }

    public function toArray()
    {
        return [
            'uuid' => $this->uuid,
            'data' => $this->data,
            'name' => $this->name
        ];
    }
}
