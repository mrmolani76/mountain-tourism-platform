# Item 19: Folio, Charge, Payment models (Module C — money domain).
# Written for DB-per-tenant tenant DBs; applied when tenant DBs are provisioned.

import django.db.models.deletion
import django.utils.timezone
from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
        ('core', '0005_building_season_settings_outlet_config'),
    ]

    operations = [
        migrations.CreateModel(
            name='Folio',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('guest_name', models.CharField(max_length=200)),
                ('stay_reference', models.CharField(blank=True, default='', max_length=100)),
                ('status', models.CharField(
                    choices=[('open', 'Open'), ('closed', 'Closed'), ('voided', 'Voided')],
                    default='open', max_length=16,
                )),
                ('currency', models.CharField(default='EUR', max_length=8)),
                ('total_amount', models.DecimalField(decimal_places=2, default=0, max_digits=12)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('voided_at', models.DateTimeField(blank=True, null=True)),
                ('voided_reason', models.CharField(blank=True, default='', max_length=200)),
                ('voided_by', models.IntegerField(blank=True, null=True)),
            ],
            options={
                'db_table': 'folio_folio',
                'ordering': ['-created_at'],
            },
        ),
        migrations.CreateModel(
            name='Charge',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('description', models.CharField(max_length=255)),
                ('amount', models.DecimalField(decimal_places=2, max_digits=12)),
                ('tax_amount', models.DecimalField(decimal_places=2, default=0, max_digits=12)),
                ('tax_rate', models.DecimalField(decimal_places=4, default=0, max_digits=6)),
                ('source', models.CharField(max_length=50)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('is_voided', models.BooleanField(default=False)),
                ('voided_at', models.DateTimeField(blank=True, null=True)),
                ('voided_reason', models.CharField(blank=True, default='', max_length=200)),
                ('voided_by', models.IntegerField(blank=True, null=True)),
                ('folio', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='charges', to='folio.folio',
                )),
                ('outlet', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='charges', to='core.outlet',
                )),
            ],
            options={
                'db_table': 'folio_charge',
                'ordering': ['-created_at'],
            },
        ),
        migrations.CreateModel(
            name='Payment',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('method', models.CharField(
                    choices=[('cash', 'Cash'), ('card', 'Card')], max_length=16,
                )),
                ('amount', models.DecimalField(decimal_places=2, max_digits=12)),
                ('reference', models.CharField(blank=True, default='', max_length=100)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('is_voided', models.BooleanField(default=False)),
                ('voided_at', models.DateTimeField(blank=True, null=True)),
                ('voided_reason', models.CharField(blank=True, default='', max_length=200)),
                ('voided_by', models.IntegerField(blank=True, null=True)),
                ('folio', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='payments', to='folio.folio',
                )),
            ],
            options={
                'db_table': 'folio_payment',
                'ordering': ['-created_at'],
            },
        ),
    ]
