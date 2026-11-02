"""BoardPlan serializers."""

from rest_framework import serializers

from modules.hotel.models import BoardPlan


class BoardPlanSerializer(serializers.ModelSerializer):
    class Meta:
        model = BoardPlan
        fields = ['id', 'name', 'code', 'description', 'is_active', 'created_at', 'updated_at']
        read_only_fields = ['id', 'created_at', 'updated_at']
