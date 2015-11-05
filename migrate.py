import os
import sqlite3

__author__ = 'pspychalski'

db_connection = None


def save_value(date, sensor, value):

    global db_connection

    c = db_connection.cursor()

    c.execute("INSERT INTO sensor_values(`Date`, `Sensor`, `Value`) VALUES(datetime('" + str(date) + "','localtime'), " + str(
        sensor) + "," + str(value) + ")")


def main():

    global db_connection

    db_connection = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data-new.db')

    c = db_connection.cursor()
    c.execute('DROP TABLE IF EXISTS sensor_values')
    c.execute('CREATE TABLE IF NOT EXISTS sensor_values(`Date` integer, `Sensor` integer, `Value` real)')

    source_connection = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

    cursor_source = source_connection.cursor()
    result = cursor_source.execute('SELECT * FROM readouts_external')

    for row in result:
        save_value(row[0], 0, row[1])
        save_value(row[0], 1, row[2])
        # print row

    result = cursor_source.execute('SELECT * FROM external_data')

    for row in result:
        save_value(row[0], 2, row[1])  # Real Pressure
        save_value(row[0], 4, row[2])  # Wind Speed
        save_value(row[0], 5, row[3])  # Wind direction
        # print row

    c.execute('CREATE INDEX IF NOT EXISTS SENSOR_A ON sensor_values(`Date`, `Sensor`)')
    c.execute('CREATE INDEX IF NOT EXISTS SENSOR_B ON sensor_values(`Sensor`)')

    db_connection.commit()

if __name__ == "__main__":
    main()
