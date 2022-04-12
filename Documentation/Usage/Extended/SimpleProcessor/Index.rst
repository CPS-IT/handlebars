.. include:: ../../../Includes.txt

.. _simple-processor:

=================
`SimpleProcessor`
=================

If it is not necessary to further process the data transferred
by TypoScript, a `SimpleProcessor` is available. This passes the
transferred data directly to the `Renderer` without any further
interaction.

.. _simple-processor-usage:

Usage
=====

.. code-block:: typoscript

   tt_content.tx_myextension_mymodule = USER
   tt_content.tx_myextension_mymodule {
       userFunc = Fr\Typo3Handlebars\DataProcessing\SimpleProcessor->process
       userFunc.templatePath = Extensions/FluidStyledContent/MyModule
   }

As you can see, the `SimpleProcessor` is directly addressed as
:typoscript:`userFunc`. It already provides the necessary functionality,
but can of course also be extended with your own requirements.

Furthermore it is necessary to indicate a template path with (this normally
happens in the `Presenter`). The `SimpleProcessor` throws an exception, if
the template path is not set or invalid.

.. _simple-processor-sources:

Sources
=======

.. seealso::

   View the sources on GitHub:

   -  `SimpleProcessor <https://github.com/CPS-IT/handlebars/blob/main/Classes/DataProcessing/SimpleProcessor.php>`__
