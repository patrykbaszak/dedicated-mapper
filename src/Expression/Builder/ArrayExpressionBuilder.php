<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Expression\Assets\Getter;
use PBaszak\MessengerMapperBundle\Expression\Assets\Setter;
use PBaszak\MessengerMapperBundle\Properties\Property;

class ArrayExpressionBuilder
{
    public function getGetter(Property $property): Getter
    {
        $name = $property->options['name'] ?? $property->originName;

        return new Getter(
            [
                'name' => $name,
                'basic' => "\${{source}}['{$name}']",
                '00000' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "{{setter}}\n"
                    . "}\n",
                '00001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '00010' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n"
                    . "}\n",
                '00011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '00100' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '00101' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '00110' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '00111' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '01000' => "\${{source}}['{$name}']",
                '01001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '01010' => "\${{var}} = \${{source}}['{$name}'];\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '01011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '01100' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '01101' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '01110' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '01111' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '10000' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "{{setter}}\n"
                    . "}\n",
                '10001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '10010' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n"
                    . "}\n",
                '10011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '10100' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{setter}}\n",
                '10101' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{setter}}\n",
                '10110' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '10111' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '11000' => "{{simpleObject}}",
                '11001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '11010' => "\${{var}} = {{simpleObject}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '11011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n"
                    . "} else {\n"
                    . "{{valueNotFoundCallbacks}}"
                    . "}\n",
                '11100' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{setter}}\n",
                '11101' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{setter}}\n",
                '11110' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
                '11111' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . "{{callbacks}}\n"
                    . "{{setter}}\n",
            ]
        );
    }

    public function getSetter(Property $property): Setter
    {
        $name = $property->options['name'] ?? $property->originName;

        return new Setter(
            [
                'name' => $name,
                'basic' => "\${{target}}['{$name}'] = {{getter}};\n",
                '000' => "\${{target}}['{$name}'] = {{getter}};\n",
                '001' => "\${{target}}['{$name}'] = \${{var}};\n",
                '010' => "\${{target}}['{$name}'] = ({{getter}}){{simpleObjectDeconstructor}};\n",
                '011' => "\${{target}}['{$name}'] = \${{var}}{{simpleObjectDeconstructor}};\n",
                '100' => "\${{target}}['{$name}'] = {{getter}};\n",
                '101' => "\${{target}}['{$name}'] = \${{var}};\n",
                '110' => "\${{target}}['{$name}'] = ({{getter}}){{simpleObjectDeconstructor}};\n",
                '111' => "\${{target}}['{$name}'] = \${{var}}{{simpleObjectDeconstructor}};\n",
            ]
        );
    }
}
