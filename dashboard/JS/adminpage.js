$(function(){

    $(document).on('focus','[tinymce="true"]', function(){
        tinymce.init({
            selector: '[tinymce="true"]',
            plugins: 'image'
         });
    })


    
    $('.manage-an').click(function(){
        tabmanageAnuncios();
    })
    $(document).on('click', '.paginator-anuncios', function(){
        if(!$(this).hasClass('active')){
            page = $(this).attr('page')
            $('#fountainG').css('display','block')
            $.ajax({
                url: BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'getAnuncios','page':page}
            }).done(function(data){
                $('#fountainG').css('display','none')
                data = JSON.parse(data)
                $('.info-content').empty().append(data.msg)   
                $('.info-content').slideDown();
            })
        }
    })
    
    $('button.manage-feedback').click(function(){
        tabManageFeedback()
    })
    $('.manage-users').click(function(){
        tabmanageUsers();
    })
    $('.manage-transacoes').click(function(){
        tabTransacoes();
    })
    $(document).on('click', '.paginator-users', function(){
        if(!$(this).hasClass('active')){
            page = $(this).attr('page')
            $.ajax({
                url: BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'manageUsers','page':page}
            }).done(function(data){
                data = JSON.parse(data)
                $('.info-content').empty().append(data.msg)   
                $('.info-content').slideDown();
            })
        }
    })
    
    $(document).on('click', '[jsAction="confirm-AddAn"]', function(){
        $('#fountainG').css('display','block')
        img = $(this).parent().parent().find('.img').find('img').prop('src').split('/')[6]
        title = $(this).parent().parent().find('[name="title"]').val()
        desc = $(this).parent().parent().find('[name="desc"]').val()
        complement = $(this).parent().parent().find('[name="complement"]').val()
        cat =  $(this).parent().parent().find('[name="cat"]').val()
        preco = $(this).parent().parent().find('[name="preco"]').val()
        if(!img){
            img = null
        }
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'insertAnuncio','img':img,'title':title,'desc':desc,'complement':complement,'cat':cat,'preco':preco}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data) 
            alertar(data.msg,'')
        })
    })
    $('.box').find('.img').hover(
        function(){
            $(this).find('i').css('opacity','0%').css('visibility','hidden')
          },
          function(){
            $(this).find('i').css('opacity','100%').css('visibility','visible')
          }
    )
    
    $(document).on('click', '.closeTab', function(){
        $('.tabopened').animate({'opacity':'0'}).css('display','none').remove()
        $('.cat-actions').find('button').removeClass('active');
    })
    $(document).on('click', '.remove-an', function(){
        $('#fountainG').css('display','block')
        id = $(this).parent().parent().attr('id')
        $('.tabopened').remove()
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-removerAnuncio','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            if(data.sucesso){
                $('.info-content').before(data.response)
                elem = document.getElementById('tabopened')
                if(!checkVisible(elem)){
                    $('html, body').animate({
                        scrollTop: $(".tabopened").offset().top
                    }, 500);
                }
            }
            if(data.msg.length > 0){
                alertar(data.msg,'')
            }
        })
    })
    $(document).on('click', '[js="excluirAnuncio"]', function(){ 
        $('#fountainG').css('display','block')
        id = $(this).parent().attr('dataid')
        $('.tabopened').remove()
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'removerAnuncio','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('.info-content').before(data.response)
                $.ajax({
                    url: BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'getAnuncios'}
                }).done(function(data){
                    $('#fountainG').css('display','none')
                    data = JSON.parse(data)
                    if(data.sucesso){
                        $('.info-content').empty().append(data.msg)   
                    }
                    
                })
            }
            if(data.msg.length > 0){
                alertar(data.msg,'')
            }
        })
    })

    $(document).on('click', '.edit-an', function(){
        $('#fountainG').css('display','block')
        id = $(this).parent().parent().attr('id')
        $('.tabopened').remove()
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-editarAnuncio','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            if(data.sucesso){
                $('.info-content').before(data.response)
                var selected = $('.tabopened .add-an').find('select').attr('selectedid')
                $('.add-an > select > option[value="'+selected+'"]').prop('selected',true)

                elem = document.getElementById('tabopened')
                if(!checkVisible(elem)){
                    $('html, body').animate({
                        scrollTop: $(".tabopened").offset().top
                    }, 500);
                }
            }
            if(data.msg.length > 0){
                alertar(data.msg,'')
            }
        })
    })

    $(document).on('click', '[js="removeimage"]', function(){
        $('#fountainG').css('display','block')
        id = $(this).parent().attr('idimg')
        obj = $(this)
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'removerImagem','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            if(data.sucesso){
                obj.parent().remove()
            }
            if(data.msg.length > 0){
                alertar(data.msg,'')
            }
        })
    })

    $(document).on('click', '[jsaction="confirm-EditAn"]', function(){
        $('#fountainG').css('display','block')
        idanuncio = $(this).parent().parent().parent().attr('idann')
        title = $(this).parent().parent().find('[name="title"]').val()
        desc = $(this).parent().parent().find('[name="desc"]').val()
        complement = $(this).parent().parent().find('[name="complement"]').val()
        cat =  $(this).parent().parent().find('[name="cat"]').val()
        preco = $(this).parent().parent().find('[name="preco"]').val()
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'type-action':'editAnuncio','id':idanuncio,'title':title,'desc':desc,'complement':complement,'cat':cat,'preco':preco}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data) 
            alertar(data.msg,'')
            if(data.sucesso){
                $('.info-content').empty();
                $.ajax({
                    url: BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'token' : sessionStorage.getItem('token'),'type-action':'getAnuncios'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('.info-content').append(data.msg).slideDown();
                })
            }
        })
    })

    $(document).on('click', '.tab-ban-us', function(){
        var id = $(this).parent().parent().attr('id')
        $('.tabopened').remove()
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-banirUser','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            if(data.msg.length > 0){
                alertar(data.msg,'')
            }
            if(data.sucesso){
                
                $('.info-content').before(data.response)
                elem = document.getElementById('tabopened')
                if(!checkVisible(elem)){
                    $('html, body').animate({
                        scrollTop: $(".tabopened").offset().top
                    }, 500);
                }
            }
        })
    })
    
    $(document).on('click', '.tab-edit-us', function(){
        var id = $(this).parent().parent().attr('id')
        $('.tabopened').remove()
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-editarUser','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            if(data.msg.length > 0){
                alertar(data.msg,'')
            }
            if(data.sucesso){
                $('.info-content').before(data.response)
                elem = document.getElementById('tabopened')
                if(!checkVisible(elem)){
                    $('html, body').animate({
                        scrollTop: $(".tabopened").offset().top
                    }, 500);
                }
            }
        })
    })

    $(document).on('click', '.edit-us', function(){
        var id = $(this).parent().attr('iduser')
        var username = $(this).parent().find('input[name="username"]').val()
        var email = $(this).parent().find('input[name="email"]').val()
        var nome = $(this).parent().find('input[name="nome"]').val()
        var sobrenome = $(this).parent().find('input[name="sobrenome"]').val()
        var cpf = $(this).parent().find('input[name="cpf"]').val()
        var telefone = $(this).parent().find('input[name="telefone"]').val()
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'editarUser','id':id,'username':username,'email':email, 'nome':nome,'sobrenome':sobrenome,'cpf':cpf, 'telefone':telefone}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            alertar(data.msg,'')
            if(data.sucesso){
                $('.info-content').empty()
                $.ajax({
                    url: BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'manageUsers'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('.info-content').append(data.msg)   
                    $('.info-content').slideDown();
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                })
                
            }
        })
    })

    $(document).on('click', '.edit-actions', function(){
        var id = $(this).parent().attr('id')
        if($('.mobile-actions[id="'+id+'"]').length == 0){
            $('#fountainG').css('display','block')
            obj = $(this)
            $.ajax({
                url:BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'edit-actions','id':id}
            }).done(function(data){
                $('#fountainG').css('display','none')
                data = JSON.parse(data)
                if(data.msg.length > 0){
                    alertar(data.msg,'')
                }
                if(data.sucesso){
                    obj.parent().after(data.response)
                }
            })
        }
    })
    $(document).on('click', '.closeEditAction', function(){
        $(this).parent().parent().slideUp().remove();
    })
    $(document).on('click', '.ban-us', function(){
        var id = $(this).parent().attr('iduser')
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'banirUser','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            alertar(data.msg,'')
            if(data.sucesso){
                $('.info-content').empty()
                $.ajax({
                    url: BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'manageUsers'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('.info-content').append(data.msg)   
                    $('.info-content').slideDown();
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                })
                
            }
        })
    
    })

    $(document).on('click', '[js="add-categorias"]', function(){
        if(!$(this).hasClass('active')){
            $('.cat-actions').find('button').removeClass('active');
            obj = $(this)
            $('#fountainG').css('display','block')
            $.ajax({
                url:BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'addCategorias'}
            }).done(function(data){
                data = JSON.parse(data)
                $('.tabopened').remove()
                if(data.sucesso){
                    obj.toggleClass('active')
                    $('.info-content').after(data.response)   
                    $('.info-content').slideDown();
                    $('#fountainG').css('display','none')
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                }
                //alertar(data.msg,'')
            })
        }
    })

    $(document).on('click', '[jsaction="confirm-AddCat"]', function(){
        var img = $(this).parent().parent().find('img').attr('data-name').split('/')[6]
        var nome = $(this).parent().parent().find('[name="nomecategoria"]').val()
        var desc = $(this).parent().parent().find('[name="desc"]').val()
        
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'confirmAddCat','img':img,'nome':nome,'desc':desc}
        }).done(function(data){
            data = JSON.parse(data)

            alertar(data.msg,'');
        })

    })
    $(document).on('click', '[js="man-categorias"]', function(){
        if(!$(this).hasClass('active')){
            obj = $(this)
            $('.cat-actions').find('button').removeClass('active');
            $('#fountainG').css('display','block')
            $.ajax({
                url:BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'manageCategorias'}
            }).done(function(data){
                data = JSON.parse(data)
                $('.tabopened').remove()
                if(data.sucesso){
                    obj.toggleClass('active')
                    $('.info-content').after(data.response)   
                    $('.info-content').slideDown();
                    $('#fountainG').css('display','none')
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                }
            })
        }
    })
    $(document).on('click', '.paginator-cats', function(){
        var page = $(this).attr('page')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageCategorias','page':page}
        }).done(function(data){
            data = JSON.parse(data)
            $('.tabopened').remove()
            if(data.sucesso){
                $('.info-content').after(data.response)   
                $('.info-content').slideDown();
                $('#fountainG').css('display','none')
            }
        })
    })

    $(document).on('click', '.add-anuncio', function(){
        if(!$(this).hasClass('active')){
            obj = $(this)
            $('.cat-actions').find('button').removeClass('active');
            $('#fountainG').css('display','block')
            $.ajax({
                url: BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'addAnuncio'}
            }).done(function(data){
                data = JSON.parse(data)
                $('.tabopened').remove()
                if(data.sucesso){
                    obj.toggleClass('active')
                    $('.info-content').after(data.msg)   
                    $('.info-content').slideDown();
                    $('#fountainG').css('display','none')
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                }
            })
        }
    })

    $(document).on('click', '[js="manage-feedbacks"]', function(){
        if(!$(this).hasClass('active')){
            obj = $(this)
            $('.cat-actions').find('button').removeClass('active');
            $('#fountainG').css('display','block')
            $.ajax({
                url: BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'manageFeedbacks'}
            }).done(function(data){
                data = JSON.parse(data)
                //$('.tabopened').remove()
                if(data.sucesso){
                    obj.toggleClass('active')
                    $('.info-content').after(data.msg)   
                    $('.info-content').slideDown();
                    $('#fountainG').css('display','none')
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                }
            })
        }
    })

    $(document).on('click', '[js="aprov-feedbacks"]', function(){
        if(!$(this).hasClass('active')){
            obj = $(this)
            $('.cat-actions').find('button').removeClass('active');
            $('#fountainG').css('display','block')
            $.ajax({
                url:BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'aprovFeedbacks'}
            }).done(function(data){
                data = JSON.parse(data)
                $('.tabopened').remove()
                if(data.sucesso){
                    obj.toggleClass('active')
                    $('.info-content').after(data.response)   
                    $('.info-content').slideDown();
                    $('#fountainG').css('display','none')
                    elem = document.getElementById('tabopened')
                    if(!checkVisible(elem)){
                        $('html, body').animate({
                            scrollTop: $(".tabopened").offset().top
                        }, 500);
                    }
                }
            })
        }
    })

    $(document).on('click', '[js="aprovar-feedback"]', function(){
        var id = $(this).parent().attr('id')
        obj = $(this)
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'confirm-aprovarFeedback','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,'')
            if(data.sucesso){
                obj.parent().remove()
                $.ajax({
                    url: BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'manageFeedback'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('#fountainG').css('display','none')
                    $('.manage-feedback').toggleClass('active')
                    $('.info-content').empty().append(data.response)   
                })
            }
        })
    })

    $(document).on('click', '.remove-feedback', function(){
        var id = $(this).parent().attr('id')
        obj = $(this)
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-excluirFeedback','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css('display','none')
            if(data.sucesso){
                $('.info-content').before(data.response)
            }
        })
    })

    $(document).on('click', '[js="confirmExcluirFeedback"]', function(){
        var id = $(this).parent().attr('feedbackid')
        obj = $(this)
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'confirmExcluirFeedback','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css('display','none')
            if(data.sucesso){
                alertar(data.msg,'')
                $.ajax({
                    url: BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'manageFeedback'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('#fountainG').css('display','none')
                    $('.manage-feedback').toggleClass('active')
                    $('.info-content').empty().append(data.response)   
                })
                $('.tabopened').remove();
            }
        })
    })

    $(document).on('click', '.destacar-feedback', function(){
        var id = $(this).parent().attr('id');
        obj = $(this)
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'destacarFeedback','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css('display','none')
            alertar(data.msg,'');
            if(data.sucesso){
                var content = obj.find('span').text(); content = content.replace(' ','')
                if(content == 'Destacar'){
                    obj.find('span').empty().append('<i class="far fa-star"></i> Tirar destaque')
                    obj.parent().toggleClass('destacado')

                }else{
                    obj.find('span').empty().append('<i class="fas fa-star"></i> Destacar')
                    obj.parent().removeClass('destacado')

                }

            }
        })

    })

    $(document).on('click', '.tab-edit-cat', function(){
        var id = $(this).parent().parent().attr('id')
        obj = $(this)
        $('.sub-tabopened').remove();
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-editarCategoria','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('.info-content').before(data.response)
                elem = document.getElementsByClassName('sub-tabopened')[0]
                if(!checkVisible(elem)){
                    $('html, body').animate({
                        scrollTop: $('.sub-tabopened').offset().top
                    }, 500);
                }
            }
        })

    })
    
    $(document).on('click', '.manage-email', function(){
        tabManageEmails();
    })

    $(document).on('click', '.confirm-editCategoria', function(){
        var id = $(this).parent().attr('editid')
        var img = $(this).parent().find("img[data-name]").prop('src'); img = img.split('/')[7] //corrigir isso dps do deploy
        var nome = $(this).parent().find('[name="nome"]').val()
        var slug = $(this).parent().find('[name="slug"]').val()
        var desc = $(this).parent().find('[name="desc"]').val()
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'editarCategoria','id':id,'nome':nome,'slug':slug,'desc':desc,'img':img}
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,'')
            if(data.sucesso){
                $.ajax({
                    url:BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'manageCategorias'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('.cats').remove()
                    if(data.sucesso){
                        $('.info-content').after(data.response)   
                        $('.info-content').slideDown();
                        $('#fountainG').css('display','none')
                    }
                })
            }
        })
    })

    $(document).on('click', '.tab-remove-cat', function(){
        var id = $(this).parent().parent().attr('id')
        obj = $(this)
        $('.sub-tabopened').remove();
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-excluirCategoria','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('.info-content').before(data.response)
                elem = document.getElementsByClassName('sub-tabopened')[0]
                if(!checkVisible(elem)){
                    $('html, body').animate({
                        scrollTop: $('.sub-tabopened').offset().top
                    }, 500);
                }
            }
        })
    })

    $(document).on('click', '.confirm-excluirCategoria', function(){
        var id = $(this).parent().attr('editid')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'excluirCategoria','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,'')
            if(data.sucesso){
                $.ajax({
                    url:BASE+'ajax/Admin.php',
                    method:'post',
                    data:{'type-action':'manageCategorias'}
                }).done(function(data){
                    data = JSON.parse(data)
                    $('.cats').remove()
                    if(data.sucesso){
                        $('.info-content').after(data.response)   
                        $('.info-content').slideDown();
                        $('#fountainG').css('display','none')
                    }
                })
            }
        })
    })

    $(document).on('keyup', '[name="search"]', function(){
        $('#fountainG').css('display','block')
        $('.box-us').empty().append('<div class="ajax-load" style="margin:20px auto; text-align:center; font-size:50px;"><i class="fa-spin fas fa-spinner" aria-hidden="true"></i></div>')
        busca = $(this).val()
        if(busca == ''){busca = false}
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageUsers','search':busca,'type':'search'}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css('display','none')
            if(data.msg.length >= 1){$('.box-us').empty().append(data.msg)}
        })
    })

    $(document).on('click', '.c-auto-complete', function(){
        var from = $(this).attr('userid') == 'all' ? 'all' : $(this).attr('userid');
        $.ajax({
            url:BASE+"ajax/Admin.php",
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'selectUserBusca','from':from}
        }).done((data)=>{
            data = JSON.parse(data)
            $('[name="buscausuario"]').attr('selecteduser',data.userid).val('').attr('placeholder','').parent().append(data.response);
            $('.p-auto-complete').empty().css('display','none')
        })
        
    })
    $(document).on('keyup', '[name="searchtransation"]', function(){
        var search = $(this).val();
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageTransacoes','searchtransation':search,'type':'search'}
        }).done((data) =>{
            data = JSON.parse(data)
            $('.content').empty().append(data.response)
        })
    })

    $(document).on('click', '.paginator-transation', function(){
        var obj = $(this);
        $('.info-content').find('.content').empty()
        var page = obj.attr('page')
        var busca = $('[name="searchtransation"]').val();
        $('#fountainG').css('display','block')
        $.ajax({
                url: BASE+'ajax/Admin.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'type-action':'manageTransacoes','page':page,'searchtransation':busca,'type':'pagination'}
        }).done(function(data){
                $('.paginator').find('button').removeClass('active')
                obj.toggleClass('active')
                $('#fountainG').css('display','none')
                data = JSON.parse(data)
                $('.manage-transacoes').toggleClass('active')
                $('.content').empty().append(data.response)
        })
    })

    $(document).on('click', '.single-transation', function(){
        obj = $(this)
        var id = obj.attr('transationid')
        $('.tabopened').remove();
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageTransacao','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css('display','none')
            if(data.sucesso){
                $('.info-content').before(data.response)
            }
        })
    })

    $(document).on('submit', '.ajax', function(e){
        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');
        from = $('[name="buscausuario"]').attr('selecteduser')
        form = form.serialize()
        if(from == '' || form.split('&')[1].split('=')[1] == ''){
            return alertar('Você precisa preencher todos os campos','');
        }
        form = form+'&from='+from
        $('#fountainG').css('display','block')
        $.ajax({
            type: "POST",
            url: url,
            data: form,
            success:function(data){
                data = JSON.parse(data)
                alertar(data.msg,data.redirect)
                $('#fountainG').css('display','none')
            },
            error:function(error){
                $('#fountainG').css('display','none')
                alertar('Aconteceu algum erro','')
            }
        })

    })

})

function changeImageAn(type){
        var imgUrl = $('[name="photo"]').prop('files')[0]
        var id = $('.tabopened').attr('idann')
        var formData = new FormData();
        
        formData.append('file',imgUrl)
        formData.append('type-action','editAnuncioAddImage');
        formData.append('id',id);
        formData.append('token',sessionStorage.getItem('token'));
        $.ajax({
        url:BASE+'ajax/Admin.php',
        method:'post',
        contentType:false,
        cache:false,
        processData:false,
        data:formData
        }).done(function(data){
        data = JSON.parse(data)
        alertar(data.msg,'')
        if(data.sucesso){
            if(type=='edit'){
                $('.tabEditAn').append('<div class="img"> <img src="'+data.newImage+'"><button js="removeimage"><i class="fas fa-times-circle" aria-hidden="true"></i></button></div>')
            }else{
                $('.img').find('img').prop('src',data.newImage).prop('data-name',data.dataname)
                $('.noimage').find('i').css('visibility','hidden').css('opacity','0')
            }
        }
    })

}
function tabManageEmails(){
    if(!$('.manage-email').hasClass('active')){
        $('.info-content').empty()
        $('.tabopened').remove()
        $('.info').find('button').removeClass('active')
        $.ajax({
            url: BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'tab-manageEmail'}
        }).done(function(data){
            data = JSON.parse(data)
            $('.manage-email').toggleClass('active')
            $('.info-content').append(data.response)   
            $('.info-content').slideDown();
        })
    }
}
function tabmanageAnuncios(){
    if(!$('.manage-an').hasClass('active')){
        $('.info-content').empty()
        $('.tabopened').remove()
        $('.info').find('button').removeClass('active')
        $.ajax({
            url: BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'getAnuncios'}
        }).done(function(data){
            data = JSON.parse(data)
            $('.manage-an').toggleClass('active')
            $('.info-content').append(data.msg)   
            $('.info-content').slideDown();
        })
    }
}
function tabmanageUsers(){
    if(!$('.manage-users').hasClass('active')){
        $('.info-content').empty()
        $('.tabopened').remove()
        $('.info').find('button').removeClass('active')
        $.ajax({
            url: BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageUsers','type':'nothingforsearch'}
        }).done(function(data){
            data = JSON.parse(data)
            $('.manage-users').toggleClass('active')
            $('.info-content').append(data.msg)   
            $('.info-content').slideDown();
    
        })
    }
}
function tabManageFeedback(){
    if(!$('.manage-feedback').hasClass('active')){
        $('.tabopened').remove()
        $('.info-content').empty()
        $('.info').find('button').removeClass('active')
        $.ajax({
            url: BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageFeedback'}
        }).done(function(data){
            data = JSON.parse(data)
            $('.manage-feedback').toggleClass('active')
            $('.info-content').append(data.response)   
            $('.info-content').slideDown();
    
        })
    }
}

function tabTransacoes(){
    if(!$('.manage-transacoes').hasClass('active')){
        $('.tabopened').remove()
        $('.info-content').empty()
        $('.info').find('button').removeClass('active')
        $.ajax({
            url: BASE+'ajax/Admin.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'type-action':'manageTransacoes','type':'tab'}
        }).done(function(data){
            data = JSON.parse(data)
            $('.manage-transacoes').toggleClass('active')
            $('.info-content').append(data.response);   
        })
    }
}
window.onload = function(){
    tabmanageAnuncios();
}

