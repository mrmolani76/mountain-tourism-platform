"""Reports ViewSets — report generation and listing (Module I)."""

from rest_framework import viewsets
from rest_framework.permissions import IsAuthenticated

from modules.core.auth import StaffSessionAuthentication
from modules.core.permissions import IsTenantStaff
from modules.reports.permissions import IsReportsStaff
from modules.reports.serializers import ReportSerializer


class ReportViewSet(viewsets.ModelViewSet):
    """Report CRUD — generated reports across modules."""

    authentication_classes = [StaffSessionAuthentication]
    permission_classes = [IsAuthenticated, IsTenantStaff, IsReportsStaff]
    serializer_class = ReportSerializer

    def get_queryset(self):
        from modules.reports.models import Report
        return Report.objects.all()
