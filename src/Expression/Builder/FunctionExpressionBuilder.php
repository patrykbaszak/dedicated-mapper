<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use PBaszak\DedicatedMapper\Contract\FunctionInterface;
use PBaszak\DedicatedMapper\Expression\Assets\Functions;

class FunctionExpressionBuilder implements FunctionInterface
{
    public function getFunction(): Functions
    {
        return new Functions(
            [
                'basic' => "function ({{sourceType}} \${{sourceName}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '0000' => "function ({{sourceType}} \${{sourceName}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '0001' => "function ({{sourceType}} \${{sourceName}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '0010' => "function ({{sourceType}} \${{sourceName}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '0011' => "function ({{sourceType}} \${{sourceName}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '0100' => "function ({{sourceType}} \${{sourceName}}) use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '0101' => "function ({{sourceType}} \${{sourceName}}) use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '0110' => "function ({{sourceType}} \${{sourceName}}) use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '0111' => "function ({{sourceType}} \${{sourceName}}) use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '1000' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '1001' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '1010' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '1011' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '1100' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}) use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '1101' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}) use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
                '1110' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}) use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{targetName}};
                }",
                '1111' => "function ({{sourceType}} \${{sourceName}}, {{pathType}} \${{pathName}}) use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{targetName}};
                }",
            ]
        );
    }
}
