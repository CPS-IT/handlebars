..  include:: /Includes.rst.txt

..  _custom-helpers:

==============
Custom helpers
==============

Handlebars helpers bring custom PHP logic into templates. The extension's
:php:`Helper` interface defines a single method:

..  code-block:: php

    public function render(\DevTheorem\Handlebars\HelperOptions $options): mixed;

Named arguments passed from the template (e.g., :handlebars:`{{greet name="Alice"}}`)
are available as :php:`$options->hash['name']`. Positional arguments
(e.g., :handlebars:`{{greet "Alice"}}`) must be declared as additional parameters
in the method signature after :php:`$options`:

..  code-block:: php

    public function render(HelperOptions $options, ?string $name = null): mixed;

The :php:`RenderingContext` can also be injected by type-hint — declare it
anywhere in the method signature before positional arguments and it is provided
automatically. It gives access to the current PSR-7 request
(:php:`$context->getRequest()`) and the full set of template variables
(:php:`$context->getVariables()`):

..  code-block:: php

    public function render(HelperOptions $options, ?RenderingContext $context = null): mixed;

The current template scope is accessible via :php:`$options->scope`, and block
helpers can call :php:`$options->fn()` and :php:`$options->inverse()` to render
their inner blocks.

..  contents::
    :local:
    :depth: 1

..  _custom-helpers-implement:

Implement a helper
==================

Implement the :php:`CPSIT\Typo3Handlebars\Renderer\Helper\Helper` interface and
place the :php:`#[AsHelper]` attribute on the class. Because the class implements
the :php:`Helper` interface, the attribute automatically resolves to the :php:`render`
method:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Helper/GreetHelper.php

    namespace Vendor\Extension\Renderer\Helper;

    use CPSIT\Typo3Handlebars\Attribute\AsHelper;
    use CPSIT\Typo3Handlebars\Renderer\Helper\Helper;
    use DevTheorem\Handlebars\HelperOptions;

    #[AsHelper('greet')]
    final readonly class GreetHelper implements Helper
    {
        public function render(HelperOptions $options): mixed
        {
            return sprintf('Hello, %s!', $options->hash['name'] ?? 'World');
        }
    }

The attribute can also be placed directly on a method, in which case the method
name is inferred automatically — no :php:`method` parameter needed. Dependency
injection works normally for all :php:`#[AsHelper]`-annotated classes:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Helper/GreetHelper.php

    use CPSIT\Typo3Handlebars\Attribute\AsHelper;
    use CPSIT\Typo3Handlebars\Renderer\Helper\Helper;
    use DevTheorem\Handlebars\HelperOptions;
    use Vendor\Extension\Domain\Model\Person;
    use Vendor\Extension\Domain\Repository\PersonRepository;

    #[AsHelper('greet')]
    final readonly class GreetHelper implements Helper
    {
        public function __construct(
            private PersonRepository $repository,
        ) {}

        public function render(HelperOptions $options): mixed
        {
            return sprintf('Hello, %s!', $options->hash['name'] ?? 'World');
        }

        #[AsHelper('greetAll')]
        public function greetAll(HelperOptions $options): mixed
        {
            return implode(PHP_EOL, array_map(
                static fn(Person $person) => sprintf('Hello, %s!', $person->getName()),
                $this->repository->findAll(),
            ));
        }
    }

..  tip::

    There's no need to implement the :php:`Helper` interface, it only serves as a
    low-barrier tool to easily get started with custom helper implementations. You
    can also just do the following:

    ..  code-block:: php
        :caption: EXT:my_extension/Classes/Renderer/Helper/GreetHelper.php

        use CPSIT\Typo3Handlebars\Attribute\AsHelper;
        use DevTheorem\Handlebars\HelperOptions;

        #[AsHelper('greet')]
        final readonly class GreetHelper
        {
            public function __invoke(HelperOptions $options): mixed
            {
                return sprintf('Hello, %s!', $options->hash['name'] ?? 'World');
            }
        }

..  _custom-helpers-use-in-template:

Use the helper in a template
=============================

Reference the helper by its identifier:

..  code-block:: handlebars

    {{greet name="Alice"}}

    {{greetAll}}

..  _custom-helpers-registration-yaml:

Alternative: Register via :file:`Services.yaml`
===============================================

If you cannot use the attribute (e.g., for a third-party class), register the
helper explicitly in :file:`Services.yaml`. Both :php:`identifier` and
:php:`method` are required:

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    services:
      Vendor\Extension\Renderer\Helper\GreetHelper:
        tags:
          - name: handlebars.helper
            identifier: 'greetAll'
            method: 'greetAll'

..  seealso::

    `Helper <https://github.com/CPS-IT/handlebars/blob/main/Classes/Renderer/Helper/Helper.php>`__
    interface source on GitHub.
