import requests
import json
import publisher
import consumer
import logging
from datetime import datetime
now = datetime.now()
currenttime = str(now)

#calls logsettings.py #https://rollbar.com/blog/logging-in-python/
logger = logging.getLogger('mylogger')

logging.basicConfig(level=logging.INFO, filename='../logs/api.log', encoding='utf-8',format='%(asctime)s:%(levelname)s:apigatherdata.py:%(message)s:')
logging.info('Current Session of API started at '+currenttime)


def doSearchAndSend(cat):
    pub = publisher.Publisher()
    cat1 = cat.replace('"', '')
    api_url = "https://www.thecocktaildb.com/api/json/v1/1/search.php?s=" + cat1
    response = requests.get(api_url)
    

    json_data = json.dumps(response.json())
    data = json.loads(json_data)
    
    result = []
    print(data)
    if(data["drinks"] is not None):
        for drink in data['drinks']:
            ingredients = []
            measures = []
            for key, item in drink.items():
                if key.startswith('strIngredient'):
                    if(item is not None):
                        if(len(item)>2):
                            ing = [key, item]
                            ingredients.append(ing)
                    
                if key.startswith('strMeasure'):
                    if(item is not None):
                        if(len(item)>2):
                            ing = [key, item]
                            measures.append(ing)

            drink_ = {
                'strDrink': drink['strDrink'],
                'strInstructions': drink['strInstructions'],
                'strDrinkThumb': drink['strDrinkThumb'], 

            }

            for ingredient in ingredients:
                drink_[ingredient[0]] = ingredient[1]
            for measure in measures:
                drink_[measure[0]] = measure[1]
            result.append(drink_)
        print(result)
        json_results= json.dumps(result)
        pub.send_msg(json_results)
        print('sent success')


def callback(ch, method, properties, body):
    print(" [x] Received msg : ", body.decode("utf-8"))
    doSearchAndSend(body.decode("utf-8"))
    ch.basic_ack(delivery_tag=method.delivery_tag)


consu = consumer.Consumer()
consu.get_msg("APISearch", callback)