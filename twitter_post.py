# -*- coding: utf-8 -*-

import tweetpony
import os
import sensor

from twitter_config import config

def main():
    api = tweetpony.API(consumer_key = config['consumer_key'], consumer_secret = config['consumer_secret'], access_token = config['access_token'], access_token_secret = config['access_token_secret'])
    user = api.user

    sensor_handler = sensor.sensor()

    temperature = sensor_handler.get_last_value(0)
    humidity = sensor_handler.get_last_value(1)
    pressure = sensor_handler.get_last_value(2)

    try:
        api.update_status(status = u'Witaj Dobra, mamy właśnie ' + unicode(str(temperature)) + u'C i ' + unicode(str(humidity)) + u'% wilgotności')
    except tweetpony.APIError as err:
        print "Oops, something went wrong! Twitter returned error #%i and said: %s" % (err.code, err.description)
    else:
        print "Yay! Your tweet has been sent!"

if __name__ == "__main__":
	main()