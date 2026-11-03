"""Stay serializers."""

from rest_framework import serializers

from modules.hotel.models import Stay


class StaySerializer(serializers.ModelSerializer):
    room_number = serializers.CharField(source='room.number', read_only=True)
    board_plan_code = serializers.CharField(source='board_plan.code', read_only=True)
    folio_id = serializers.IntegerField(source='folio.id', read_only=True)

    class Meta:
        model = Stay
        fields = [
            'id', 'room', 'room_number', 'guest_name', 'check_in', 'check_out',
            'status', 'board_plan', 'board_plan_code', 'folio', 'folio_id',
            'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'room_number', 'board_plan_code', 'folio_id',
                    'created_at', 'updated_at']


class StayCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Stay
        fields = ['room', 'guest_name', 'check_in', 'check_out', 'board_plan']

    def validate(self, attrs):
        check_in = attrs.get('check_in')
        check_out = attrs.get('check_out')
        if check_in and check_out and check_out <= check_in:
            raise serializers.ValidationError({'check_out': 'check_out must be after check_in.'})
        return attrs
