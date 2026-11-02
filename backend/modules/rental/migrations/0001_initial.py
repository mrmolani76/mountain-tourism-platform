# Item 22: Rental model (Module E — equipment rental).
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
            name='Rental',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('item_name', models.CharField(max_length=200)),
                ('item_sku', models.CharField(blank=True, default='', max_length=64)),
                ('checked_out_at', models.DateTimeField(auto_now_add=True)),
                ('returned_at', models.DateTimeField(blank=True, null=True)),
                ('status', models.CharField(
                    choices=[('out', 'Out'), ('returned', 'Returned'), ('damaged', 'Damaged')],
                    default='out', max_length=16,
                )),
                ('damage_fee', models.DecimalField(decimal_places=2, default=0, max_digits=10)),
                ('deposit', models.DecimalField(decimal_places=2, default=0, max_digits=10)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('folio', models.ForeignKey(
                    blank=True, null=True,
                    on_delete=django.db.models.deletion.SET_NULL,
                    related_name='rentals', to='folio.folio',
                )),
                ('outlet', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='rentals', to='core.outlet',
                )),
                ('staff', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='rentals', to='core.staff',
                )),
            ],
            options={
                'db_table': 'rental_rental',
                'ordering': ['-checked_out_at'],
            },
        ),
    ]
