from rest_framework.pagination import LimitOffsetPagination, PageNumberPagination


class CustomPagination(LimitOffsetPagination):
    max_limit = 100

