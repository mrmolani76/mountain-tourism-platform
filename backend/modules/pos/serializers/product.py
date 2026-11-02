"""Product serializers."""

from rest_framework import serializers

from modules.pos.models import Product


class ProductSerializer(serializers.ModelSerializer):
    outlet_name = serializers.CharField(source='outlet.name', read_only=True)
    category_name = serializers.CharField(source='category.name', read_only=True)

    class Meta:
        model = Product
        fields = [
            'id', 'outlet', 'outlet_name', 'category', 'category_name',
            'name', 'sku', 'price', 'tax_rate', 'is_active',
            'created_at', 'updated_at',
        ]
        read_only_fields = ['id', 'outlet_name', 'category_name', 'created_at', 'updated_at']


class ProductCreateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Product
        fields = ['outlet', 'category', 'name', 'sku', 'price', 'tax_rate']
