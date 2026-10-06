<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\DiscoveredModel;
use Revolution\Copilot\Types\Rpc\DiscoveredModelList;
use Revolution\Copilot\Types\Rpc\ModelProviderConfigurationPlan;
use Revolution\Copilot\Types\Rpc\ModelProviderInstance;
use Revolution\Copilot\Types\Rpc\ModelProviderInstanceReference;

/**
 * Model listing and configuration planning for discovered provider instances.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingProviderModels
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function list(ModelProviderInstanceReference|array $instance): DiscoveredModelList
    {
        $reference = $instance instanceof ModelProviderInstanceReference
            ? $instance->toArray()
            : ModelProviderInstanceReference::fromArray($instance)->toArray();

        return DiscoveredModelList::fromArray(
            $this->client->request('session.providers.models.list', [
                'sessionId' => $this->sessionId,
                'instance' => $reference,
            ]),
        );
    }

    public function prepareConfiguration(ModelProviderInstance|array $instance, DiscoveredModel|array $model): ModelProviderConfigurationPlan
    {
        $instanceArray = $instance instanceof ModelProviderInstance
            ? $instance->toArray()
            : ModelProviderInstance::fromArray($instance)->toArray();
        $modelArray = $model instanceof DiscoveredModel
            ? $model->toArray()
            : DiscoveredModel::fromArray($model)->toArray();

        return ModelProviderConfigurationPlan::fromArray(
            $this->client->request('session.providers.models.prepareConfiguration', [
                'sessionId' => $this->sessionId,
                'instance' => $instanceArray,
                'model' => $modelArray,
            ]),
        );
    }
}
