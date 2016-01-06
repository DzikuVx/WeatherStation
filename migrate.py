import os
import sqlite3
import sensor

__author__ = 'pspychalski'

db_connection = None

def main():

    sensor_handler = sensor.sensor()

    global db_connection

    db_connection = sensor_handler.get_db_connection()

    c = db_connection.cursor()
    c.execute('DROP TABLE IF EXISTS sensor_values')
    c.execute('CREATE TABLE IF NOT EXISTS sensor_values(`Date` integer, `Sensor` integer, `Value` real)')

    source_connection = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

    cursor_source = source_connection.cursor()
    result = cursor_source.execute('SELECT * FROM readouts_external')

    for row in result:
        sensor_handler.insert_no_commit(row[0], 0, row[1]) # Temperature
        sensor_handler.insert_no_commit(row[0], 1, row[2]) # Humidity
        # print row

    result = cursor_source.execute('SELECT * FROM external_data')

    for row in result:
        sensor_handler.insert_no_commit(row[0], 2, row[1])  # Real Pressure
        sensor_handler.insert_no_commit(row[0], 4, row[2])  # Wind Speed
        sensor_handler.insert_no_commit(row[0], 5, row[3])  # Wind direction
        # print row

    c.execute('CREATE INDEX IF NOT EXISTS SENSOR_A ON sensor_values(`Date`, `Sensor`)')

    db_connection.commit()

if __name__ == "__main__":
    main()
