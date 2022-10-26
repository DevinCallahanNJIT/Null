import pika
import json


class Publisher:
    def __init__(self):
        credentials = pika.PlainCredentials('webadmin', 'ChangeLater')
        self.connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='10.10.200.1',
            port=5672,
            credentials=credentials,
            virtual_host='API',
            socket_timeout=5,
        ))

    def send_msg(self, data):
        channel = self.connection.channel()
        channel.queue_declare(queue='API',durable=True)

        json_str = json.dumps(data, ensure_ascii=False)

        channel.basic_publish(exchange='amq.direct', routing_key='', body=json_str)
        self.connection.close()
