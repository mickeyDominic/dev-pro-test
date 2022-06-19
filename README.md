# dev-pro-test is a repository to house code I wrote as part of a proficiency test for DEVPROX.

The test has two part (test 1 and test 2).
I completed both tests using PHP and HTML and built the solution on my own using the using WAMPSEVER on a Windows machine.
I have created two folders (“test 1” and “test 2”) to house solutions for the respective tests.
I have had to edit the PHP.ini file to allow my solution to run and produce desired output, I made changes to:
  1.	Maximum execution time of each script (max_execution_time) from default 120 seconds to 43200 seconds to allow for the generation of 1 million records.
  2.	Maximum size of POST data that PHP will accept (post_max_size) from default 8MB to 40MB to allow a user to upload a csv file of with 1 million records.
  3.	Maximum allowed size for uploaded files (upload_max_filesize) from default to 40MB to allow a user to upload a csv file of with 1 million records.
  4.	Maximum amount of memory a script may consume (memory_limit) from default 128M to 512MB after numerous fatal errors when trying to create 1 million records.

Using WAMPSEVER for virtual host on your PC, you may navigate to the localhost folder (usually C:\wamp\www\), paste the folders and run them through the browser by visiting http://localhost/test1 and http://localhost/test2 respectively. 
In test 1, the program will launch and prompt you for data then yield a relevant message after processing your data.
In test 2, the program will prompt you to enter the number of records you wish to generate. It will then go on to generate the record(s) and yield a message upon completion or failure to generate the records. If the generation was successful, the program will create an “output.csv” file in the same directory as the test file program (C:\wamp\www\test2) and then move on to prompt you to select the file you wish to upload. You may then select the appropriate file and upload it by clicking the POST button. A relevant message will be printed after the program has analysed the file.

I believe my solution for tests 1 and 2 meet requirements. Test 1 will run and yield results with the server on default settings, however, test 2 may need the above changes to be implemented for the solution to run smoothly and yield desired results.
