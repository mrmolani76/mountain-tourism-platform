"""Shared JSON envelope helpers and DRF exception handler.

Envelope format (success):

    {
      "success": true,
      "data": { ... },
      "error": null,
      "meta": { ... }
    }

Envelope format (error):

    {
      "success": false,
      "data": null,
      "error": { "code": "...", "message": "...", "details": { ... } },
      "meta": { ... }
    }
"""

from rest_framework.views import exception_handler as drf_exception_handler


def success_response(data=None, meta=None, status=None):
    """Build a success envelope for DRF Response."""
    body = {
        'success': True,
        'data': data if data is not None else {},
        'error': None,
        'meta': meta or {},
    }
    from rest_framework.response import Response
    return Response(body, status=status)


def error_response(code, message, details=None, status=None):
    """Build an error envelope for DRF Response."""
    body = {
        'success': False,
        'data': None,
        'error': {
            'code': code,
            'message': message,
            'details': details or {},
        },
        'meta': {},
    }
    from rest_framework.response import Response
    return Response(body, status=status or 400)


def custom_exception_handler(exc, context):
    """Wrap DRF exceptions in the consistent JSON envelope.

    Falls back to DRF's default exception_handler, then rewrites the
    response body into the envelope shape so every error looks the same.
    """
    response = drf_exception_handler(exc, context)

    if response is None:
        return None

    # Extract a simple code + message from the DRF response.
    if isinstance(response.data, dict):
        detail = response.data.get('detail', response.data)
        code = response.data.get('code', 'error')
    else:
        detail = response.data
        code = 'error'

    message = str(detail) if not isinstance(detail, dict) else detail.get('message', str(detail))
    details = detail if isinstance(detail, dict) else {}

    response.data = {
        'success': False,
        'data': None,
        'error': {
            'code': code,
            'message': message,
            'details': details,
        },
        'meta': {},
    }
    return response
