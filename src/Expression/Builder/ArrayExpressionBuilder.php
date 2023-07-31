<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Builder;

use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Getter;
use PBaszak\DedicatedMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Setter;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

class ArrayExpressionBuilder extends AbstractBuilder implements SetterInterface, GetterInterface
{
    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        return new InitialExpression("\${{targetName}} = [];\n");
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return 'array';
    }

    public function getTargetType(Blueprint $blueprint): string
    {
        return 'array';
    }

    /**
     *  0 => isSimpleObject
     *  1 => throwExceptionOnMissingRequiredValue
     *  2 => hasDefaultValue
     *  3 => hasCallbacks
     *  4 => hasValueNotFoundCallbacks.
     */
    public function getGetter(Property $property): Getter
    {
        $name = $property->options['name'] ?? $property->originName;
        $property->options['name'] = $name;

        return new Getter(
            [
                'basic' => "\${{source}}['{$name}']",
                '00000' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . '{{setter}}'
                    . "}\n",
                '00001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '00010' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . '{{callbacks}}'
                    . '{{setter}}'
                    . "}\n",
                '00011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . '{{callbacks}}'
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '00100' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '00101' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '00110' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '00111' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '01000' => "if (!array_key_exists('{$name}', \${{source}})) {\n"
                    . "throw new \Error('Missing required value: \"{$name}\"');\n"
                    . "}\n"
                    . '{{setter}}',
                '01001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '01010' => "if (!array_key_exists('{$name}', \${{source}})) {\n"
                    . "throw new \Error('Missing required value: \"{$name}\"');\n"
                    . "}\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '01011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = \${{source}}['{$name}'];\n"
                    . '{{callbacks}}'
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '01100' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '01101' => "\${{source}}['{$name}'] ?? {{defaultValue}}",
                '01110' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '01111' => "\${{var}} = \${{source}}['{$name}'] ?? {{defaultValue}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '10000' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . '{{setter}}'
                    . "}\n",
                '10001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '10010' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}'
                    . "}\n",
                '10011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '10100' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{setter}}',
                '10101' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{setter}}',
                '10110' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '10111' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '11000' => '{{simpleObject}}',
                '11001' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '11010' => "\${{var}} = {{simpleObject}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '11011' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . '{{callbacks}}'
                    . '{{setter}}'
                    . "} else {\n"
                    . '{{valueNotFoundCallbacks}}'
                    . "}\n",
                '11100' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{setter}}',
                '11101' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{setter}}',
                '11110' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
                '11111' => "if (array_key_exists('{$name}', \${{source}})) {\n"
                    . "\${{var}} = {{simpleObject}};\n"
                    . "} else {\n"
                    . "\${{var}} = {{defaultValue}};\n"
                    . "}\n"
                    . '{{callbacks}}'
                    . '{{setter}}',
            ]
        );
    }

    /**
     * 0 => isCollection
     * 1 => hasFunction
     * 2 => hasPathUsed
     * 3 => isSimpleObject
     * 4 => hasSimpleObjectDeconstructor
     * 5 => isVarVariableUsed
     */
    public function getSetter(Property $property): Setter
    {
        $name = $property->options['name'] ?? $property->originName;
        $property->options['name'] = $name;

        return new Setter(
            [
                'basic' => "\${{target}}['{$name}'] = {{getter}};\n",
                '000000' => "\${{target}}['{$name}'] = {{getter}};\n",
                '000001' => "\${{target}}['{$name}'] = \${{var}};\n",
                '000010' => "\${{target}}['{$name}'] = ({{getter}}){{simpleObjectDeconstructor}};\n",
                '000011' => "\${{target}}['{$name}'] = \${{var}}{{simpleObjectDeconstructor}};\n",
                '000100' => "\${{target}}['{$name}'] = {{getter}};\n",
                '000101' => "\${{target}}['{$name}'] = \${{var}};\n",
                '000110' => "\${{target}}['{$name}'] = ({{getter}}){{simpleObjectDeconstructor}};\n",
                '000111' => "\${{target}}['{$name}'] = \${{var}}{{simpleObjectDeconstructor}};\n",
                '001000' => "\${{target}}['{$name}'] = {{getter}};\n",
                '001001' => "\${{target}}['{$name}'] = \${{var}};\n",
                '001010' => "\${{target}}['{$name}'] = ({{getter}}){{simpleObjectDeconstructor}};\n",
                '001011' => "\${{target}}['{$name}'] = \${{var}}{{simpleObjectDeconstructor}};\n",
                '001100' => "\${{target}}['{$name}'] = {{getter}};\n",
                '001101' => "\${{target}}['{$name}'] = \${{var}};\n",
                '001110' => "\${{target}}['{$name}'] = ({{getter}}){{simpleObjectDeconstructor}};\n",
                '001111' => "\${{target}}['{$name}'] = \${{var}}{{simpleObjectDeconstructor}};\n",
                '010000' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}({{getter}});\n",
                '010001' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}});\n",
                '010010' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(({{getter}}){{simpleObjectDeconstructor}});\n",
                '010011' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}}{{simpleObjectDeconstructor}});\n",
                '010100' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}({{getter}});\n",
                '010101' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}});\n",
                '010110' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(({{getter}}){{simpleObjectDeconstructor}});\n",
                '010111' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}}{{simpleObjectDeconstructor}});\n",
                '011000' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}({{getter}}, \${{pathName}} . \".{$name}\");\n",
                '011001' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}}, \${{pathName}} . \".{$name}\");\n",
                '011010' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(({{getter}}){{simpleObjectDeconstructor}}, \${{pathName}} . \".{$name}\");\n",
                '011011' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}}{{simpleObjectDeconstructor}}, \${{pathName}} . \".{$name}\");\n",
                '011100' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}({{getter}}, \${{pathName}} . \".{$name}\");\n",
                '011101' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}}, \${{pathName}} . \".{$name}\");\n",
                '011110' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(({{getter}}){{simpleObjectDeconstructor}}, \${{pathName}} . \".{$name}\");\n",
                '011111' => '${{functionVariable}} = {{function}};'
                    . "\${{target}}['{$name}'] = \${{functionVariable}}(\${{var}}{{simpleObjectDeconstructor}}, \${{pathName}} . \".{$name}\");\n",
                '100100' => "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110000' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110001' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110010' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110011' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110100' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110101' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110110' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '110111' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item);\n"
                    . "}\n",
                '111000' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111001' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111010' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}((\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111011' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111100' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111101' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111110' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}((\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
                '111111' => "\${{functionVariable}} = {{function}};\n"
                    . "\${{target}}['{$name}'] = [];\n"
                    . "foreach ({{getter}} as \$index => \$item) {\n"
                    . "\t\${{target}}['{$name}'][] = \${{functionVariable}}(\$item, \${{pathName}} . \".{$name}.{\$index}\");\n"
                    . "}\n",
            ]
        );
    }
}
