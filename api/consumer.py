import pika


class Consumer:
    def __init__(self):
        credentials = pika.PlainCredentials('webadmin', 'ChangeLater')
        self.connection = pika.BlockingConnection(pika.ConnectionParameters(
            host='10.10.200.1',
            port=5672,
            credentials=credentials,
            virtual_host='API',
            socket_timeout=5,
        ))

    def get_msg(self, queue, callback):
        channel = self.connection.channel()
        channel.basic_consume(
            on_message_callback=callback,
            queue=queue,
        )

        channel.start_consuming()