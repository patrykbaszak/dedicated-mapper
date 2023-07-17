<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Assets\Getter;
use PBaszak\MessengerMapperBundle\Expression\Assets\Setter;
use PBaszak\MessengerMapperBundle\Properties\Property;

class ArrayExpressionBuilder extends AbstractBuilder implements SetterInterface, GetterInterface
{
    public function getGetter(Property $property): Getter
    {
        $name = $property->options['name'] ?? $property->originName;
        $property->options['name'] = $name;

        return new Getter(
            [
                'basic' => "\${{source}}['{$name}']",
                '00000' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."{{setter}}"
                    ."}\n",
                '00001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '00010' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = \${{source}}['{$name}'];\n"
                    ."{{callbacks}}"
                    ."{{setter}}"
                    ."}\n",
                '00011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = \${{source}}['{$name}'];\n"
                    ."{{callbacks}}"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '00100' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '00101' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '00110' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '00111' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '01000' => "\${{source}}['{$name}']",
                '01001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '01010' => "\${{var}} = \${{source}}['{$name}'];\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '01011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = \${{source}}['{$name}'];\n"
                    ."{{callbacks}}"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '01100' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '01101' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '01110' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '01111' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '10000' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."{{setter}}"
                    ."}\n",
                '10001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '10010' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}"
                    ."}\n",
                '10011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '10100' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{setter}}",
                '10101' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{setter}}",
                '10110' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '10111' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '11000' => '{{simpleObject}}',
                '11001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '11010' => "\${{var}} = {{simpleObject}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '11011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."{{callbacks}}"
                    ."{{setter}}"
                    ."} else {\n"
                    .'{{valueNotFoundCallbacks}}'
                    ."}\n",
                '11100' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{setter}}",
                '11101' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{setter}}",
                '11110' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
                '11111' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    ."\${{var}} = {{simpleObject}};\n"
                    ."} else {\n"
                    ."\${{var}} = {{defaultValue}};\n"
                    ."}\n"
                    ."{{callbacks}}"
                    ."{{setter}}",
            ]
        );
    }

    public function getSetter(Property $property): Setter
    {
        $name = $property->options['name'] ?? $property->originName;
        $property->options['name'] = $name;

        return new Setter(
            [
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
