<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\PluginInstallationReviewAction;

/**
 * Safe, verified Agent Plugin review to present before applying an install plan.
 *
 * @experimental
 */
readonly class PluginInstallationReview implements Arrayable
{
    /**
     * @param  PluginInstallationFileReview[]  $files
     */
    public function __construct(
        public PluginInstallationReviewAction $action,
        public InstallationCatalogueIdentity $catalogue,
        public array $files,
        public bool $installsDisabled,
        public string $manifestContent,
        public string $manifestPath,
        public string $name,
        public PluginInstallationSource $source,
        public PluginInstallationLocation $target,
        public int $totalBytes,
        public ?string $description = null,
        public ?string $version = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            action: PluginInstallationReviewAction::from($data['action']),
            catalogue: InstallationCatalogueIdentity::fromArray($data['catalogue']),
            files: array_map(
                fn (array $file): PluginInstallationFileReview => PluginInstallationFileReview::fromArray($file),
                $data['files'],
            ),
            installsDisabled: $data['installsDisabled'],
            manifestContent: $data['manifestContent'],
            manifestPath: $data['manifestPath'],
            name: $data['name'],
            source: PluginInstallationSource::fromArray($data['source']),
            target: PluginInstallationLocation::fromArray($data['target']),
            totalBytes: $data['totalBytes'],
            description: $data['description'] ?? null,
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'action' => $this->action->value,
            'catalogue' => $this->catalogue->toArray(),
            'files' => array_map(
                fn (PluginInstallationFileReview $file): array => $file->toArray(),
                $this->files,
            ),
            'installsDisabled' => $this->installsDisabled,
            'manifestContent' => $this->manifestContent,
            'manifestPath' => $this->manifestPath,
            'name' => $this->name,
            'source' => $this->source->toArray(),
            'target' => $this->target->toArray(),
            'totalBytes' => $this->totalBytes,
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        if ($this->version !== null) {
            $data['version'] = $this->version;
        }

        return $data;
    }
}
