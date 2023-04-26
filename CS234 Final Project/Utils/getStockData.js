
//Function is called to get the API data from a ticker provided from user's portfolio.
async function getData(ticker, apikey, apihost){
    if(ticker == ''){
        return;
    }
    const options = {
        method: 'GET',
        headers: {
            'X-RapidAPI-Key': apikey,
            'X-RapidAPI-Host': apihost
        }
    };
    //The main function in this file, kind of obtuse, but it gets the job done. Basically just returns a formatted chart row of the api data returned from the stock ticker.

    let data = await fetch('https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2/get-summary?symbol='+ ticker + '&region=US', options)
        .then(response => response.json())
        .then(response => {
            const data = response;

            var str = ticker;
            var tag = document.createElement('td');
            var text = document.createTextNode(str);
            tag.appendChild(text);
            var element = document.getElementById(ticker);
            element.appendChild(tag);

            str = data['price']['shortName'];
            tag = document.createElement('td');
            text = document.createTextNode(str);
            tag.appendChild(text);
            element = document.getElementById(ticker);
            element.appendChild(tag);

            str = "$" + data['price']['regularMarketPrice']['fmt'];
            tag = document.createElement('td');
            text = document.createTextNode(str);
            tag.appendChild(text);
            element = document.getElementById(ticker);
            element.appendChild(tag);

            str = "$" + data['price']['regularMarketPreviousClose']['fmt'];
            tag = document.createElement('td');
            text = document.createTextNode(str);
            tag.appendChild(text);
            element = document.getElementById(ticker);
            element.appendChild(tag);

            str = data['price']['regularMarketChangePercent']['fmt'];
            tag = document.createElement('td');
            text = document.createTextNode(str);
            tag.appendChild(text);
            element = document.getElementById(ticker);
            element.appendChild(tag);

            str = data['price']['regularMarketVolume']['fmt'];
            tag = document.createElement('td');
            text = document.createTextNode(str);
            tag.appendChild(text);
            element = document.getElementById(ticker);
            element.appendChild(tag);
        })
        .catch(err => console.error(err));
    return;
}