from decorators.singleton import singleton
from models.Phone import Phone

@singleton
class PhonesManager:
    def __init__(self):
        self.phones = {}

    def list(self):
        return list(self.phones.values())

    def set(self, phones):
        self.phones = phones

        return self

    def has(self, id):
        return id in self.phones

    def get(self, id):
        return self.phones.get(id)

    def add(self, phone: Phone):
        self.phones[phone.id] = phone

        return self
