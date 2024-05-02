# Dedicated Mapper (Bundle*) #
**I present to You the fastest PHP Dedicated Mapper ever created!**<br>
It's even **31** times faster than **JMS Serializer** and even **43** times faster than **Symfony Serializer** in denormalization!<br>
<sub>*The package supports Symfony Bundle system but not require to be used with Symfony.</sub>

## Usage
```php
<?php

declare(strict_types=1);

namespace App;

use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder as ArrayBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassExpressionBuilder as ClassBuilder;
use PBaszak\DedicatedMapper\MapperService;

class Test
{
    private string $name;
}

$data = [
    'name' => 'test';
];
$mapper = new MapperService('/app/var/mapper/');

/** @var Test $test */
$test = $mapper->map(
    $data, 
    Test::class,
    new ArrayBuilder(), # You have to specify $data type, available options: 'array', 'object', 'class object'. In this case it's a `array`
    new ClassBuilder(), # You have to specify output type, in this case it's `class object` based on the blueprint class `Test`.
);
```

## How it works?
The **Dedicated Mapper** generates php file which looks like this one *(it's only example for specific case)*:

```php
<?php

declare(strict_types=1);

return function (array $data): PBaszak\DedicatedMapper\Tests\Performance\SimpleData {
    $ref_df1d1d13 = new ReflectionClass(PBaszak\DedicatedMapper\Tests\Performance\SimpleData::class);
    /** @var PBaszak\DedicatedMapper\Tests\Performance\SimpleData $output */
    $output = $ref_df1d1d13->newInstanceWithoutConstructor();

    if (array_key_exists('name', $data)) {
        $ref_df1d1d13->getProperty('name')->setValue($output, $data['name']);
    }

    return $output;
};
```

and using it in the mapping process.

## Report ##
### Environment Details
- **PHP**: 8.2.7 (from Dockerfile and without xdebug)
- **Docker**: Docker version 20.10.12, build 20.10.12-0ubuntu2~20.04.1
- **OS**: WSL2 - Ubuntu 20.04.5 LTS, Windows 10 Pro 22H2 19045.3324
- **CPU**: Intel(R) Core(TM) i5-10400 CPU @ 2.90GHz
- **RAM**: DDR4 32,0GB 2667MHz
- **SSD**: Samsung 970 EVO Plus 500GB M.2 2280 PCI-E x4 Gen3 NVMe

> Note:  Each test was run 100 times.<br>
> If you want to run the test yourself:
> ```sh
> git clone https://github.com/patrykbaszak/dedicated-mapper.git
> bash start.sh
> docker exec composer test:performance
> ``` 

### Comparison Results

#### JMS Serializer vs Dedicated Mapper

| Test Case                         | Metric | JMS Serializer        | Dedicated Mapper       | Performance Gain      |
|:---------------------------------:|:------:|----------------------:|-----------------------:|----------------------:|
| Build & Use                       | avg    | 0.00073563575744629 s | 0.00022567272186279 s  | 3.26x faster          |
|                                   | min    | 0.00061202049255371 s | 0.0001990795135498 s   | 3.07x faster          |
|                                   | max    | 0.006666898727417 s   | 0.0012781620025635 s   | 5.22x faster          |
| Use (one time)                    | avg    | 0.00064053297042847 s | 0.00020017862319946 s  | 3.2x faster           |
|                                   | min    | 0.00055694580078125 s | 0.00018501281738281 s  | 3.01x faster          |
|                                   | max    | 0.0044717788696289 s  | 0.0002751350402832 s   | 16.25x faster         |
| Second Use (Same Data)            | avg    | 0.00031213998794556 s | 5.3181648254395E-5 s   | 5.87x faster          |
|                                   | min    | 0.00029683113098145 s | 5.0067901611328E-5 s   | 5.93x faster          |
|                                   | max    | 0.0003669261932373 s  | 7.8916549682617E-5 s   | 4.65x faster          |
| Second Use (Different Data)       | avg    | 0.00062076330184937 s | 5.8262348175049E-5 s   | 10.65x faster         |
|                                   | min    | 0.0005500316619873 s  | 5.4836273193359E-5 s   | 10.03x faster         |
|                                   | max    | 0.0029869079589844 s  | 9.5129013061523E-5 s   | 31.4x faster          |


#### Symfony Serializer vs Dedicated Mapper

| Test Case                         | Metric | Symfony Serializer     | Dedicated Mapper       | Performance Gain      |
|:---------------------------------:|:------:|-----------------------:|-----------------------:|----------------------:|
| Build & Use                       | avg    | 0.0021615481376648 s   | 0.00021748304367065 s  | 9.94x faster          |
|                                   | min    | 0.0019149780273438 s   | 9.3221664428711E-5 s   | 20.54x faster         |
|                                   | max    | 0.01357889175415 s     | 0.00031518936157227 s  | 43.08x faster         |
| Use (one time)                    | avg    | 0.0019077062606812 s   | 0.0002018141746521 s   | 9.45x faster          |
|                                   | min    | 0.0017900466918945 s   | 0.00018596649169922 s  | 9.63x faster          |
|                                   | max    | 0.0043408870697021 s   | 0.0002748966217041 s   | 15.79x faster         |
| Second Use (Same Data)            | avg    | 0.0011084413528442 s   | 6.0606002807617E-5 s   | 18.29x faster         |
|                                   | min    | 0.0010659694671631 s   | 5.5074691772461E-5 s   | 19.35x faster         |
|                                   | max    | 0.0017890930175781 s   | 0.0001060962677002 s   | 16.86x faster         |
| Second Use (Different Data)       | avg    | 0.0019117307662964 s   | 6.1674118041992E-5 s   | 31x faster            |
|                                   | min    | 0.0017828941345215 s   | 5.6028366088867E-5 s   | 31.82x faster         |
|                                   | max    | 0.0042397975921631 s   | 0.00010395050048828 s  | 40.79x faster         |
| Build, Use & Validation           | avg    | 0.0023907327651978 s   | 0.00046631574630737 s  | 5.13x faster          |
|                                   | min    | 0.002216100692749 s    | 0.00043201446533203 s  | 5.13x faster          |
|                                   | max    | 0.007519006729126 s    | 0.00083398818969727 s  | 9.02x faster          |
| Use & Validation (one time)       | avg    | 0.0022232341766357 s   | 0.00038869619369507 s  | 5.72x faster          |
|                                   | min    | 0.0020699501037598 s   | 0.0003659725189209 s   | 5.66x faster          |
|                                   | max    | 0.0052480697631836 s   | 0.0004730224609375 s   | 11.09x faster         |
| Second Use & Validation (Same Data) | avg | 0.0012864065170288 s   | 0.00018531322479248 s  | 6.94x faster          |
|                                   | min    | 0.0012338161468506 s   | 0.00017285346984863 s  | 7.14x faster          |
|                                   | max    | 0.0020201206207275 s   | 0.0002751350402832 s   | 7.34x faster          |
| Second Use & Validation (Different Data) | avg | 0.0021827912330627 s | 0.00018846273422241 s  | 11.58x faster         |
|                                   | min    | 0.00205397605896 s     | 0.00017499923706055 s  | 11.74x faster         |
|                                   | max    | 0.0047998428344727 s   | 0.0003211498260498 s   | 14.95x faster         |

<sub>*Tables generated using Chat GPT-4 based on test data. This information is here because the chat is not a perfect tool and could mess up the measured times for example.</sub>
