"""Report serializers."""

from rest_framework import serializers

from modules.reports.models import Report


class ReportSerializer(serializers.ModelSerializer):
    generator_name = serializers.CharField(source='generated_by.name', read_only=True)

    class Meta:
        model = Report
        fields = [
            'id', 'report_type', 'title', 'parameters', 'result',
            'generated_by', 'generator_name', 'generated_at', 'is_active',
        ]
        read_only_fields = ['id', 'generator_name', 'generated_at']
