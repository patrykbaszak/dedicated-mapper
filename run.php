<?php

for ($i = 0; $i < 64; $i++) {
    $key = str_pad(decbin($i), 6, '0', STR_PAD_LEFT);

    echo "/** @test */\n";
    echo "public function testGetter$key(): void\n";
    echo "{\n";
    echo "    \$key = '$key';\n";
    echo "    \$this->assertIsOutputAsigned(\$key);\n";

    if ($key[0] == '1') {
        echo "    \$this->assertIsAsignedCollection(\$key);\n";
    }

    if ($key[1] == '1') {
        echo "    \$this->assertIsAsignedFunction(\$key);\n";
    }

    if ($key[2] == '1') {
        echo "    \$this->assertIsAsignedPath(\$key);\n";
    }

    if ($key[3] == '1') {
        echo "    \$this->assertIsAsignedSimpleObject(\$key);\n";
    }

    if ($key[4] == '1') {
        echo "    \$this->assertIsAsignedSimpleObjectDeconstructor(\$key);\n";
    }

    if ($key[5] == '1') {
        echo "    \$this->assertIsAssignedVarVariable(\$key);\n";
    }

    echo "}\n\n";
}
