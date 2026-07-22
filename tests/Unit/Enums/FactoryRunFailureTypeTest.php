<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FactoryRunFailureType;

describe('FactoryRunFailureType', function () {
    it('has factory_limit_reached and factory_resume_declined cases', function () {
        expect(FactoryRunFailureType::FACTORY_LIMIT_REACHED->value)->toBe('factory_limit_reached')
            ->and(FactoryRunFailureType::FACTORY_RESUME_DECLINED->value)->toBe('factory_resume_declined');
    });

    it('can create from string', function () {
        expect(FactoryRunFailureType::from('factory_limit_reached'))->toBe(FactoryRunFailureType::FACTORY_LIMIT_REACHED)
            ->and(FactoryRunFailureType::from('factory_resume_declined'))->toBe(FactoryRunFailureType::FACTORY_RESUME_DECLINED);
    });
});
