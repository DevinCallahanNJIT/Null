import requests
import json
import publisher
import consumer


def doSearchAndSend(cat):
    pub = publisher.Publisher()

    api_url = "https://www.thecocktaildb.com/api/json/v1/1/search.php?s=" + cat
    response = requests.get(api_url)

    json_data = json.dumps(response.json())
    data = json.loads(json_data)

    result = []

    for drink in data['drinks']:
        ingredients = []
        for key, item in drink.items():
            if key.startswith('strIngredient') and item is not None:
                ing = [key, item]
                ingredients.append(ing)

        drink_ = {
            'idDrink': drink['idDrink'],
            'strDrink': drink['strDrink'],
            'strTags': drink['strTags'],
            'strCategory': drink['strCategory'],
            'strAlcoholic': drink['strAlcoholic'],
            'strInstructions': drink['strInstructions'],
        }

        for ingredient in ingredients:
            drink_[ingredient[0]] = ingredient[1]
        result.append(drink_)

    pub.send_msg(result)
    print('sent success')


def callback(ch, method, properties, body):
    print(" [x] Received msg : ", body.decode("utf-8"))
    doSearchAndSend(body.decode("utf-8"))
    ch.basic_ack(delivery_tag=method.delivery_tag)


consu = consumer.Consumer()
consu.get_msg("APISearch", callback)