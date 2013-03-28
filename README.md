# SendegaAPI - PHP
==============

Sendega SMS API is a basic wrapper for sending SMS and MMS through Sendega.com. It can also be used to do number lookup.

## Usage

    <?php
    require_once("Sendega.php")
    $Sendega = new Sendega("ID", "Password", "Sender");
    print $Sendega->SMS("Number", "Message"); // Sends SMS and prints the message ID
    print $Sendega->MMS("Number", "Message", "Attachment"); // Sends MMS with "Attachment" attached (be vcf or image) and prints message ID.
    
    var_dump($Sendega->Lookup("Number")); // Looks up number info for Number
    ?>

## Todo

 * Implement premium SMS and MMS (charging recipient)
 * Implement logging
 * World domination

## License

Copyright 2013 ON IT AS and other contributors.

http://on-it.no

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
