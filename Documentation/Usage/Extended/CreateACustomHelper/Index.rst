.. include:: ../../../Includes.txt

.. _create-a-custom-helper:

========================
Create a custom `Helper`
========================

Any `Helper` implemented in the frontend via JavaScript and used in Handlebars
templates must also be replicated in PHP. For this purpose, the extension
provides an interface :php:`Fr\Typo3Handlebars\Renderer\Helper\HelperInterface`.

.. note::

   In the following examples, a `Helper` is created with the identifier `greet`.
   The associated class name is :php:`Vendor\Extension\Renderer\Helper\GreetHelper`.

.. _implementation-options:

Implementation options
======================

There are several ways to implement `Helpers`. To understand how `Helpers` are
resolved in the `Renderer`, it is worth taking a look at the responsible
`Trait <https://github.com/CPS-IT/handlebars/blob/main/Classes/Traits/HandlebarsHelperTrait.php>`__.

Basically every registered `Helper` must be
`callable <https://www.php.net/manual/en/function.is-callable.php>`__. This means
that both globally defined functions and invokable classes as well as class
methods are possible. See what options are available in the following examples.

.. _global-function:

Global function
---------------

Any globally registered function can be used as a `Helper`, provided that it
is recognized by the registered PHP autoloader.

::

   function greet(array $context): string
   {
       return sprintf('Hello, %s!', $context['hash']['name']);
   }

.. _invokable-class:

Invokable class
---------------

Invokable classes can also be used as `Helpers`. For this it is necessary
that they implement the method :php:`__invoke()`.

::

   # Classes/Renderer/Helper/GreetHelper.php

   namespace Vendor\Extension\Renderer\Helper;

   use Fr\Typo3Handlebars\Renderer\Helper\HelperInterface;

   class GreetHelper implements HelperInterface
   {
       public function __invoke(array $context): string
       {
           return sprintf('Hello, %s!', $context['hash']['name']);
       }
   }

.. _class-method:

Class method (recommended)
--------------------------

The most convenient variant is the implementation of a class and an
associated method. This also allows, for example, the use of dependency
injection, provided that a corresponding registration of the `Helper`
takes place :ref:`via the service container <automatic-registration>`.

::

   # Classes/Renderer/Helper/GreetHelper.php

   namespace Vendor\Extension\Renderer\Helper;

   use Fr\Typo3Handlebars\Renderer\Helper\HelperInterface;
   use Vendor\Extension\Domain\Repository\PersonRepository;

   class GreetHelper implements HelperInterface
   {
       private PersonRepository $repository;

       public function __construct(PersonRepository $repository)
       {
           $this->repository = $repository;
       }

       public function greetById(array $context): string
       {
           $name = (int)$this->getNameById($context['hash']['userId']);

           return sprintf('Hello, %s!', $name);
       }

       private function getNameById(int $userId): string
       {
           return $this->repository->findByUid($userId)->getName();
       }
   }

.. _registration:

Registration
============

`Helpers` can be registered either via configuration in the :file:`Services.yaml`
file or directly via the `Renderer` (if the default `Renderer` is used).

.. _automatic-registration:

Automatic registration via the service container (recommended)
--------------------------------------------------------------

The recommended way to register `Helpers` is to use the global service container.
This ensures that the `Helpers` are always available in the `Renderer`. To achieve
this, add the following lines to your :file:`Services.yaml` file:

.. code-block:: yaml

   # Configuration/Services.yaml

   services:
     Vendor\Extension\Renderer\Helper\GreetHelper:
       tags:
         - name: handlebars.helper
           identifier: 'greet'
           method: 'greetById'

.. warning::

   **Only for implementation as class method**

   Note that registration using a tag is available only for the implementation as
   :ref:`class-method`. For all other implementations, a direct method call must
   currently still be registered:

   .. code-block:: yaml

      # Configuration/Services.yaml

      services:
        handlebars.renderer_extended:
          parent: handlebars.renderer
          calls:
            # Global function
            - registerHelper: ['greet', 'greet']
            # or invokable class
            - registerHelper: ['greet', '@Vendor\Extension\Renderer\Helper\GreetHelper']

        Fr\Typo3Handlebars\Renderer\RendererInterface:
          alias: 'handlebars.renderer_extended'

The `identifier` configuration specifies how the `Helper` should be named and
referenced. It will then be used in Handlebars templates when calling the
registered `Helper`. An example template could look like this:

.. code-block:: handlebars

   {{ greet id=1 }}

It will result in: `Hello, Bob`!

`method` defines the class method to be used as a callback in combination with
the configured class name. The above example leads to the registration of a
new `Helper` named `greet` which provides a callback
:php:`Vendor\Extension\Renderer\Helper\GreetHelper::greetById`.

.. _manual-registration:

Manual registration
-------------------

In addition to automatic registration, `Helpers` can also be registered
manually at any time. For this purpose it is necessary to initialize the
`Renderer` beforehand. Then a `Helper` can be registered with the
:php:`registerHelper()` method and thus made available in the `Renderer`:

::

   $renderer->registerHelper(
       'greet',
       \Vendor\Extension\Renderer\Helper\GreetHelper::class . '::greetById'
   );

.. _create-a-custom-helper-sources:

Sources
=======

.. seealso::

   View the sources on GitHub:

   -  `HelperInterface <https://github.com/CPS-IT/handlebars/blob/main/Classes/Renderer/Helper/HelperInterface.php>`__
   -  `HandlebarsHelperPass <https://github.com/CPS-IT/handlebars/blob/main/Classes/DependencyInjection/HandlebarsHelperPass.php>`__
