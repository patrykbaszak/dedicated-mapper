# Dedicated Mapper (Bundle*) #
**I present to You the fastest PHP Dedicated Mapper ever created!**<br>
It's even **37** times faster than **JMS Serializer** and even **2500** times faster than **Symfony Serializer** in denormalization!<br>
<sub>*The package supports Symfony Bundle system but not require to be used with Symfony.</sub>

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

| Test Case                         | Metric | JMS Serializer         | Dedicated Mapper       | Performance Gain      |
|:---------------------------------:|:------:|-----------------------:|-----------------------:|----------------------:|
| Build & Use                       | avg    | 0.0010306453704834 s   | 0.00020055055618286 s  | 5.14x faster          |
|                                   | min    | 0.00090694427490234 s  | 0.00018501281738281 s  | 4.9x faster           |
|                                   | max    | 0.0069160461425781 s   | 0.001025915145874 s    | 6.74x faster          |
| Use (one time)                    | avg    | 0.00077522277832031 s  | 0.00018158197402954 s  | 4.27x faster          |
|                                   | min    | 0.00063705444335938 s  | 0.00016999244689941 s  | 3.75x faster          |
|                                   | max    | 0.0062828063964844 s   | 0.00025200843811035 s  | 24.93x faster         |
| Second Use (Same Data)            | avg    | 0.00035294532775879 s  | 4.8666000366211E-5 s   | 7.25x faster          |
|                                   | min    | 0.00033092498779297 s  | 4.6014785766602E-5 s   | 7.19x faster          |
|                                   | max    | 0.00045204162597656 s  | 8.2015991210938E-5 s   | 5.51x faster          |
| Second Use (Different Data)       | avg    | 0.00069396495819092 s  | 5.272388458252E-5 s    | 13.16x faster         |
|                                   | min    | 0.0006108283996582 s   | 4.7922134399414E-5 s   | 12.75x faster         |
|                                   | max    | 0.0049350261688232 s   | 0.00013303756713867 s  | 37.09x faster         |

#### Symfony Serializer vs Dedicated Mapper

| Test Case                         | Metric | Symfony Serializer     | Dedicated Mapper       | Performance Gain      |
|:---------------------------------:|:------:|-----------------------:|-----------------------:|----------------------:|
| Build & Use                       | avg    | 0.14696174383163 s     | 0.00022732496261597 s  | 646.48x faster        |
|                                   | min    | 0.14242696762085 s     | 9.2983245849609E-5 s   | 1531.75x faster       |
|                                   | max    | 0.16421699523926 s     | 0.00034117698669434 s  | 481.32x faster        |
| Use (one time)                    | avg    | 0.14963219642639 s     | 0.0002064323425293 s   | 724.85x faster        |
|                                   | min    | 0.14522504806519 s     | 0.00019288063049316 s  | 752.93x faster        |
|                                   | max    | 0.16044902801514 s     | 0.00027704238891602 s  | 579.15x faster        |
| Second Use (Same Data)            | avg    | 0.0007201623916626 s   | 5.2292346954346E-5 s   | 13.77x faster         |
|                                   | min    | 0.00066900253295898 s  | 4.7922134399414E-5 s   | 13.96x faster         |
|                                   | max    | 0.00089192390441895 s  | 0.0001518726348877 s   | 5.87x faster          |
| Second Use (Different Data)       | avg    | 0.15525386810303 s     | 6.2630176544189E-5 s   | 2478.9x faster        |
|                                   | min    | 0.14795207977295 s     | 5.8174133300781E-5 s   | 2543.26x faster       |
|                                   | max    | 0.16473388671875 s     | 9.0122222900391E-5 s   | 1827.89x faster       |
