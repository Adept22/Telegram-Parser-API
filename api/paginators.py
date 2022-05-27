from rest_framework.pagination import LimitOffsetPagination, PageNumberPagination


class MyPagination(LimitOffsetPagination):
    max_limit = 100

