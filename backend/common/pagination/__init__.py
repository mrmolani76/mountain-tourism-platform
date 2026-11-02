"""Shared DRF pagination classes."""

from rest_framework.pagination import PageNumberPagination
from rest_framework.response import Response


class StandardResultsSetPagination(PageNumberPagination):
    """Default pagination for list endpoints.

    Page size is 20, overridable via ?page_size= (max 100).
    """

    page_size = 20
    page_size_query_param = 'page_size'
    max_page_size = 100

    def get_paginated_response(self, data):
        return Response({
            'success': True,
            'data': data,
            'error': None,
            'meta': {
                'page': self.page.number,
                'page_size': self.page_size,
                'total_pages': self.paginator.num_pages,
                'total_count': self.paginator.count,
                'has_next': self.page.has_next(),
                'has_previous': self.page.has_previous(),
            },
        })
