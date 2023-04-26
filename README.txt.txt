Made by: Aiden Nelson
Date Made: 12/4/2022

VIDEO DEMO: 
	https://drive.google.com/file/d/1RiaxdNZyfgLh_aT8Zzt60SnD7E2MiB1h/view?usp=sharing

IMPORTANT DETAILS:
	You must import the sql file to a database called 'project' for the website to be
	functional. 

	The sql connecting needs to be on port 8889, as this is the required port
	when developing the project (and, as such, is hardcoded into the connection
	function.

	If this is not possible, then in the functions.php file, there is a single 
	connection function that changes the port number (for sql) for the entire project.
	Please change this, or the project will not operate at all.

	There are also API keys in one of the PHP files. As these are kind of private
	to me, I would ask that you do not share them. There is not any payment or 
	personal data tied to these, but still, it would be appreciated so that the 
	API that I am using for free does not max out the number of requests I can make.

	Also, please do not mess around in the Utils file too much. This is where the drive
	functions.php file is, as well as the browser icon. These are not meant to be directly
	accessed by any end user. Thanks!

	Lastly, the only configured user at the time of submitting this is admin, with a pw of admin
	as well. This account is the only one configured to be able to remove and add users
	from the profile page.	

	Thank you, view below for a rundown of how the site works.


Operating Flow of the Project:

	The project is a stock tracker, where users can add stocks to their
	portfolio, and then select stocks from there to see live prices/stats
	for.

	Utilizing PHP and Javascript, the website is fully interactive and secure.

	When a user signs up, they will first see a page stating they have no
	stocks. Directing to the profile page where there is a dropdown to add
	stocks from the S&P500.

	Users, once they have added stocks to their portfolio, can navigate to be able 
	to see the live stock prices button, or just go to the homepage, and will
	then be able to select the individual stocks from their portfolio they want to see 
	the prices/stats for. 

	Users can also remove their stocks later on.

	Thank you, if you have any more questions, please visit the above attached google 
	drive link to watch a short video of how the website works, and a demo of its functionality!


Aiden Nelson