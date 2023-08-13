# Dedicated Mapper (Bundle*) #
**I present to You the fastest PHP Dedicated Mapper ever created!**<br>
It's even 37 times faster than **JMS Serializer** and 2500 times faster than **Symfony Serializer** in denormalization!<br>
<sub>*The package supports Symfony Bundle system but not require to be used with Symfony.</sub>

## Report ##
```info
PHP: 8.2.7 (from Dockerfile and without xdebug)
Docker: Docker version 20.10.12, build 20.10.12-0ubuntu2~20.04.1
OS: WSL2 - Ubuntu 20.04.5 LTS, Windows 10 Pro 22H2 19045.3324
CPU: Intel(R) Core(TM) i5-10400 CPU @ 2.90GHz
RAM: DDR4 32,0GB 2667MHz
SSD: Samsung 970 EVO Plus 500GB M.2 2280 PCI-E x4 Gen3 NVMe

# Each test was run 100 times. 
```

If you want to run the test yourself:
```sh
git clone https://github.com/patrykbaszak/dedicated-mapper.git
bash start.sh
docker exec composer test:performance
```

### Build and use comparison test: ###

**JMS Serializer**:<br>
**avg**: `0.0010306453704834 s`<br>
**min**: `0.00090694427490234 s`<br>
**max**: `0.0069160461425781 s`<br>

**Dedicated Mapper**:<br>
**avg**: `0.00020055055618286 s` (5.14 times faster)<br>
**min**: `0.00018501281738281 s` (4.9 times faster)<br>
**max**: `0.001025915145874 s` (6.74 times faster)<br>
### Just use one time comparison test: ### 

**JMS Serializer**:<br>
**avg**: `0.00077522277832031 s`<br>
**min**: `0.00063705444335938 s`<br>
**max**: `0.0062828063964844 s`<br>

**Dedicated Mapper**:<br>
**avg**: `0.00018158197402954 s` (4.27 times faster)<br>
**min**: `0.00016999244689941 s` (3.75 times faster)<br>
**max**: `0.00025200843811035 s `(24.93 times faster)<br>
### Second use comparison test (same data): ### 

**JMS Serializer**:<br>
**avg**: `0.00035294532775879 s`<br>
**min**: `0.00033092498779297 s`<br>
**max**: `0.00045204162597656 s`<br>

**Dedicated Mapper**:<br>
**avg**: `4.8666000366211E-5 s` (7.25 times faster)<br>
**min**: `4.6014785766602E-5 s` (7.19 times faster)<br>
**max**: `8.2015991210938E-5 s` (5.51 times faster)<br>
### Second use comparison test (different data): ### 

**JMS Serializer**:<br>
**avg**: `0.00069396495819092 s`<br>
**min**: `0.0006108283996582 s`<br>
**max**: `0.0049350261688232 s`<br>

**Dedicated Mapper**:<br>
**avg**: `5.272388458252E-5 s` (13.16 times faster)<br>
**min**: `4.7922134399414E-5 s` (12.75 times faster)<br>
**max**: `0.00013303756713867 s` (37.09 times faster)<br>
### Build and use comparison test: ### 

**Symfony Serializer**:<br>
**avg**: `0.14696174383163 s`<br>
**min**: `0.14242696762085 s`<br>
**max**: `0.16421699523926 s`<br>

**Dedicated Mapper**:<br>
**avg**: `0.00022732496261597 s` (646.48 times faster)<br>
**min**: `9.2983245849609E-5 s` (1531.75 times faster)<br>
**max**: `0.00034117698669434 s` (481.32 times faster)<br>
### Just use one time comparison test: ### 

**Symfony Serializer**:<br>
**avg**: `0.14963219642639 s`<br>
**min**: `0.14522504806519 s`<br>
**max**: `0.16044902801514 s`<br>

**Dedicated Mapper**:<br>
**avg**: `0.0002064323425293 s` (724.85 times faster)<br>
**min**: `0.00019288063049316 s` (752.93 times faster)<br>
**max**: `0.00027704238891602 s` (579.15 times faster)<br>
### Second use comparison test (same data): ### 

**Symfony Serializer**:<br>
**avg**: `0.0007201623916626 s`<br>
**min**: `0.00066900253295898 s`<br>
**max**: `0.00089192390441895 s`<br>

**Dedicated Mapper**:<br>
**avg**: `5.2292346954346E-5 s` (13.77 times faster)<br>
**min**: `4.7922134399414E-5 s` (13.96 times faster)<br>
**max**: `0.0001518726348877 s` (5.87 times faster)<br>
### Second use comparison test (different data): ### 

**Symfony Serializer**:<br>
**avg**: `0.15525386810303 s`<br>
**min**: `0.14795207977295 s`<br>
**max**: `0.16473388671875 s`<br>

**Dedicated Mapper**:<br>
**avg**: `6.2630176544189E-5 s` (2478.9 times faster)<br>
**min**: `5.8174133300781E-5 s` (2543.26 times faster)<br>
**max**: `9.0122222900391E-5 s` (1827.89 times faster)<br>
