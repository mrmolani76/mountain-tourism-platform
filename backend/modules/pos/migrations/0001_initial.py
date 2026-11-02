# Item 21: ProductCategory, Product, Order, OrderLine models (Module D — POS).
# Written for DB-per-tenant tenant DBs; applied when tenant DBs are provisioned.

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
        ('core', '0005_building_season_settings_outlet_config'),
        ('folio', '0001_initial'),
    ]

    operations = [
        migrations.CreateModel(
            name='ProductCategory',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=100)),
                ('code', models.CharField(max_length=32, unique=True)),
                ('description', models.CharField(blank=True, default='', max_length=255)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'pos_product_category',
                'ordering': ['code'],
            },
        ),
        migrations.CreateModel(
            name='Product',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=200)),
                ('sku', models.CharField(blank=True, default='', max_length=64)),
                ('price', models.DecimalField(decimal_places=2, max_digits=10)),
                ('tax_rate', models.DecimalField(decimal_places=4, default=0, max_digits=6)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('category', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='products', to='pos.productcategory',
                )),
                ('outlet', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='products', to='core.outlet',
                )),
            ],
            options={
                'db_table': 'pos_product',
                'ordering': ['outlet', 'name'],
            },
        ),
        migrations.CreateModel(
            name='Order',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('status', models.CharField(
                    choices=[('open', 'Open'), ('paid', 'Paid'), ('voided', 'Voided')],
                    default='open', max_length=16,
                )),
                ('total_amount', models.DecimalField(decimal_places=2, default=0, max_digits=12)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('folio', models.ForeignKey(
                    blank=True, null=True,
                    on_delete=django.db.models.deletion.SET_NULL,
                    related_name='pos_orders', to='folio.folio',
                )),
                ('outlet', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='orders', to='core.outlet',
                )),
                ('staff', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='pos_orders', to='core.staff',
                )),
            ],
            options={
                'db_table': 'pos_order',
                'ordering': ['-created_at'],
            },
        ),
        migrations.CreateModel(
            name='OrderLine',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('quantity', models.PositiveIntegerField(default=1)),
                ('unit_price', models.DecimalField(decimal_places=2, max_digits=10)),
                ('tax_amount', models.DecimalField(decimal_places=2, default=0, max_digits=10)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('is_voided', models.BooleanField(default=False)),
                ('voided_at', models.DateTimeField(blank=True, null=True)),
                ('voided_reason', models.CharField(blank=True, default='', max_length=200)),
                ('voided_by', models.IntegerField(blank=True, null=True)),
                ('order', models.ForeignKey(
                    on_delete=django.db.models.deletion.CASCADE,
                    related_name='lines', to='pos.order',
                )),
                ('product', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='order_lines', to='pos.product',
                )),
            ],
            options={
                'db_table': 'pos_order_line',
                'ordering': ['-created_at'],
            },
        ),
    ]
