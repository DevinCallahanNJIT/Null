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
        channel.queue_declare(queue='API', durable=True)

        channel.basic_publish(exchange='amq.direct', routing_key='', body=data)
        self.connection.close()