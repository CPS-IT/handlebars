..  include:: /Includes.rst.txt

..  _data-processor-resolve-markers:

===============
resolve-markers
===============

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ResolveMarkersProcessor`

Replaces marker-style keys (e.g., :typoscript:`###NAV_ITEMS###`) in the
processed data with the values stored under those keys. The typical pattern is
to use a marker as a named placeholder early in the chain — either as the
:typoscript:`as` target of a preceding processor or directly in a
:typoscript:`variables` entry — and then resolve all markers to clean variable
names in a final step. This keeps intermediate processors decoupled from the
variable names the template expects.

..  code-block:: typoscript

    tt_content.my_element = HANDLEBARSTEMPLATE
    tt_content.my_element {
        templateName = MyElement

        dataProcessing {
            # Declare the expected output slots early using markers — these
            # names will become the final template variables
            10 = menu
            10 {
                as = ###mainNavigation###
                levels = 2
            }

            20 = menu
            20 {
                as = ###footerLinks###
                special = directory
                special.value = 42
            }

            # Resolve all markers to clean variable names in one final step
            90 = resolve-markers
            90.removeNonMatchingMarkers = 1
        }
    }

After processing, the template receives :typoscript:`mainNavigation` and
:typoscript:`footerLinks` as clean variable names. The markers at the top of
the chain serve as upfront documentation of what the template expects, while
the processors that follow fill those slots independently.

..  _data-processor-resolve-markers-properties:

Properties
==========

:typoscript:`pattern`
    Regular expression used to identify marker keys. The first capture
    group becomes the resolved variable name.
    Default: :typoscript:`###(.*?)###`

:typoscript:`removeNonMatchingMarkers`
    Boolean. When :typoscript:`1`, variable keys that still match the
    marker pattern after resolution (i.e., no value was found for them)
    are removed from the processed data. Default: :typoscript:`0`.
