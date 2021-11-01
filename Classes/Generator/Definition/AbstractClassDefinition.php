<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\Generator\Definition;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\ClassReflection;

/**
 * AbstractClassDefinition
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
abstract class AbstractClassDefinition implements ClassDefinitionInterface
{
    protected function decorateGeneratorMethod(): string
    {
        [, , $caller] = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);

        return sprintf('\\%s::%s', $caller['class'], $caller['function']);
    }

    /**
     * @param class-string $className
     * @param string $methodName
     * @return MethodGenerator
     */
    protected function getMethodGeneratorFromReflection(string $className, string $methodName): MethodGenerator
    {
        $classReflection = new ClassReflection($className);
        $methodReflection = $classReflection->getMethod($methodName);
        $methodGenerator = MethodGenerator::fromReflection($methodReflection);
        $methodGenerator->removeFlag(MethodGenerator::FLAG_INTERFACE);
        $methodGenerator->removeFlag(MethodGenerator::FLAG_ABSTRACT);

        return $methodGenerator;
    }
}
