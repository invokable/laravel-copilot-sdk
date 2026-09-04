<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Origin of an effective session model change.
 */
enum ModelChangeSource: string
{
    /** The user selected a model directly with `/model <id>`. */
    case MODEL_COMMAND = 'model_command';

    /** The user selected the model with `/settings`. */
    case SETTINGS_COMMAND = 'settings_command';

    /** The user selected the model with the `/config` alias. */
    case CONFIG_COMMAND = 'config_command';

    /** The user selected the model in the model picker, including the picker opened by bare `/model`. */
    case MODEL_PICKER = 'model_picker';

    /** Organization-managed settings selected the model. */
    case MANAGED_SETTINGS = 'managed_settings';

    /** Repository settings selected the model. */
    case REPO_SETTINGS = 'repo_settings';

    /** Startup model resolution selected the model. */
    case STARTUP = 'startup';

    /** Selecting an agent selected its configured model. */
    case AGENT = 'agent';

    /** Entering, leaving, or reconfiguring plan mode selected the model. */
    case PLAN_MODE = 'plan_mode';

    /** The runtime selected the model automatically, such as rate-limit recovery or refusal fallback. */
    case AUTOMATIC = 'automatic';
}
