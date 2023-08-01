<?php

for ($i = 0; $i < 64; ++$i) {
    $key = str_pad(decbin($i), 6, '0', STR_PAD_LEFT);

    echo "/** @test */\n";
    echo "public function testGetter$key(): void\n";
    echo "{\n";
    echo "    \$key = '$key';\n";
    echo "    \$this->assertIsOutputAsigned(\$key);\n";

    if ('1' == $key[0]) {
        echo "    \$this->assertIsAsignedCollection(\$key);\n";
    }

    if ('1' == $key[1]) {
        echo "    \$this->assertIsAsignedFunction(\$key);\n";
    }

    if ('1' == $key[2]) {
        echo "    \$this->assertIsAsignedPath(\$key);\n";
    }

    if ('1' == $key[3]) {
        echo "    \$this->assertIsAsignedSimpleObject(\$key);\n";
    }

    if ('1' == $key[4]) {
        echo "    \$this->assertIsAsignedSimpleObjectDeconstructor(\$key);\n";
    }

    if ('1' == $key[5]) {
        echo "    \$this->assertIsAssignedVarVariable(\$key);\n";
    }

    echo "}\n\n";
}
