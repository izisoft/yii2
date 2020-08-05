# Copyright (c) 1999-2000 Comune di Prato - Prato - Italy
# Author: Gabriele Bartolini - Prato - Italy <angusgb@users.sourceforge.net>


include $(top_srcdir)/Makefile.config

PHPINCLUDES =	db.inc \
		english.inc \
		footer.inc \
		functions.inc \
		global.inc \
		header.inc \
		italian.inc \
		mysqldb.inc 

EXTRA_DIST = $(PHPINCLUDES)

install-data-local:	all
	@echo "Installing php includes ..."
	$(mkinstalldirs) $(DESTDIR)$(PHP_DIR)/include
	@for i in $(PHPINCLUDES); do \
		$(INSTALL_DATA) $(top_srcdir)/php/include/$$i $(DESTDIR)$(PHP_DIR)/include/$$i; echo $(DESTDIR)$(PHP_DIR)/include/$$i; \
	done

