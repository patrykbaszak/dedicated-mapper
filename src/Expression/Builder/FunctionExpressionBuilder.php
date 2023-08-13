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
                'basic' => "function ({{sourceType}} \${{source}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '0000' => "function ({{sourceType}} \${{source}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '0001' => "function ({{sourceType}} \${{source}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '0010' => "function ({{sourceType}} \${{source}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '0011' => "function ({{sourceType}} \${{source}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '0100' => "function ({{sourceType}} \${{source}}) use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '0101' => "function ({{sourceType}} \${{source}}) use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '0110' => "function ({{sourceType}} \${{source}}) use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '0111' => "function ({{sourceType}} \${{source}}) use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '1000' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = ''): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '1001' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = ''): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '1010' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = ''): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '1011' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = ''): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '1100' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = '') use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '1101' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = '') use ({{useStatements}}): {{targetType}} {
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
                '1110' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = '') use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    \nreturn \${{target}};
                }",
                '1111' => "function ({{sourceType}} \${{source}}, {{pathType}} \${{pathName}} = '') use ({{useStatements}}): {{targetType}} {
                    {{initialExpression}}
                    {{expressions}}
                    {{finalExpression}}
                    \nreturn \${{target}};
                }",
            ]
        );
    }
}
