# Copyright (c) 1999-2000 Comune di Prato - Prato - Italy
# Author: Gabriele Bartolini - Prato - Italy <angusgb@users.sourceforge.net>

include $(top_srcdir)/Makefile.config

SUBDIRS = include

PHPSCRIPTS = \
	drop.php \
	index.php \
	listlinks.php \
	listurls.php \
	qryurls.php \
	showlink.php \
	showurl.php

EXTRA_DIST = $(PHPSCRIPTS)

install-data-local:	all
	@echo "Installing php scripts ..."
	$(mkinstalldirs) $(DESTDIR)$(PHP_DIR)
	@for i in $(PHPSCRIPTS); do \
		$(INSTALL_DATA) $(top_srcdir)/php/$$i $(DESTDIR)$(PHP_DIR)/$$i; echo $(DESTDIR)$(PHP_DIR)/$$i; \
	done
        #done && test -z "$$fail"
