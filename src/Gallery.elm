module Gallery
    exposing
        ( Config
        , Model
        , initial
        , Msg
        , update
        , open
        , modal
        , thumbnails
        )

{-| This module is used to render images as gallery thumbnails & lightboxes.

TODO:

  - Store list in model? Currently don't have it so thumbnails & next/prev
    could have different sets of images.
  - Use elm-style-animation for transitions. Transition closing modal, sliding
    between images, & size changes.
  - Dim current & show spinner while waiting for next/prev image to load
  - Esc & arrow keys to close/navigate
  - Show row of thumbnails below image or bottom of screen.

-}

import Html exposing (Html, div, text, img, a)
import Html.Keyed as Keyed
import Html.Attributes exposing (class, tabindex, src, href, style)
import Html.Events exposing (onClick, onInput, onSubmit, onWithOptions, defaultOptions, keyCode)
import Json.Decode as Decode


type alias Config a =
    { thumbnailUrl : a -> String
    , imageUrl : a -> String
    }


{-| Nothing while uninitialized with a list & selection.

TODO: Something more descriptive like
= Uninitialized
| Initialized list selected next previous isOpen

-}
type alias Model a =
    Maybe (SubModel a)


type alias SubModel a =
    { selected : a
    , next : a
    , previous : a
    }


initial : Model a
initial =
    Nothing


type Msg a
    = Noop
    | Close
    | Select a
    | Next
    | Previous


update : Msg a -> Model a -> List a -> Model a
update msg m l =
    case msg of
        Noop ->
            m

        Close ->
            Nothing

        Select s ->
            calcNextPrev l s

        Next ->
            Maybe.andThen (.next >> calcNextPrev l) m

        Previous ->
            Maybe.andThen (.previous >> calcNextPrev l) m


{-| Build a Model with the correct Next & Previous fields for the selected item.
-}
calcNextPrev : List a -> a -> Model a
calcNextPrev allItems selected =
    let
        orMaybe mx my =
            case ( mx, my ) of
                ( Just _, _ ) ->
                    mx

                ( Nothing, Just _ ) ->
                    my

                ( Nothing, Nothing ) ->
                    Nothing
    in
        List.foldl
            (\i acc ->
                case acc of
                    ( prev, True, Nothing ) ->
                        ( prev, True, Just i )

                    ( _, True, Just _ ) ->
                        acc

                    ( prev, False, next ) ->
                        if i == selected then
                            ( prev, True, next )
                        else
                            ( Just i, False, next )
            )
            ( Nothing, False, Nothing )
            allItems
            |> \( p, _, n ) ->
                Maybe.map2 (,)
                    (orMaybe p
                        (List.drop (List.length allItems - 1) allItems
                            |> List.head
                        )
                    )
                    (orMaybe n
                        (List.head allItems)
                    )
                    |> Maybe.map
                        (\( previous, next ) ->
                            { selected = selected
                            , next = next
                            , previous = previous
                            }
                        )


{-| Render the Modal. This should always be done, even if the Modal has not
been opened.
-}
modal : Config a -> Model a -> Html (Msg a)
modal c model =
    let
        ( modal, backdrop ) =
            case model of
                Nothing ->
                    ( div
                        [ class "modal fade align-items-center justify-content-center"
                        , tabindex -1
                        ]
                        [ div [ class "modal-dialog" ]
                            [ div [ class "modal-content" ]
                                [ div [ class "modal-body" ]
                                    []
                                ]
                            ]
                        ]
                    , div [ class "modal-backdrop fade" ] []
                    )

                Just { selected } ->
                    ( div
                        [ class "modal fade align-items-center justify-content-center show"
                        , tabindex -1
                        , closeModalOnClick
                        , closeModalOnEsc
                        , ignoreScroll
                        , ignoreMove
                        ]
                        [ div
                            [ class "modal-dialog"
                            ]
                            [ div [ class "modal-content" ]
                                [ div [ class "modal-body" ]
                                    [ img
                                        [ src <| c.imageUrl selected
                                        , class "img-fluid"
                                        , style [ ( "max-height", "90vh" ) ]
                                        ]
                                        []
                                    , Html.span [ class "modal-prev", previousOnClick ]
                                        [ Html.span [ class "fa-stack fa-2x" ]
                                            [ Html.i [ class "fa fa-circle fa-stack-2x" ] []
                                            , Html.i [ class "fa fa-chevron-left fa-stack-1x fa-inverse" ] []
                                            ]
                                        ]
                                    , Html.span [ class "modal-next", nextOnClick ]
                                        [ Html.span [ class "fa-stack fa-2x" ]
                                            [ Html.i [ class "fa fa-circle fa-stack-2x" ] []
                                            , Html.i [ class "fa fa-chevron-right fa-stack-1x fa-inverse" ] []
                                            ]
                                        ]
                                    , Html.span [ class "fa-stack fa-2x modal-close", closeModalOnClick ]
                                        [ Html.i [ class "fa fa-circle fa-stack-2x" ] []
                                        , Html.i [ class "fa fa-times fa-stack-1x fa-inverse" ] []
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    , div [ class "modal-backdrop fade show", closeModalOnClick ] []
                    )
    in
        Keyed.node "div" [ class "gallery-modal" ] [ ( "gallery-modal", modal ), ( "gallery-backdrop", backdrop ) ]


{-| Render thumbnails using the given list.
-}
thumbnails : Config a -> List a -> Html (Msg a)
thumbnails c =
    let
        renderItem item =
            div [ class "col-24 col-md-12 col-lg-6 mb-2 text-center" ]
                [ a
                    [ href <| c.imageUrl item
                    , openModalOnClick item
                    ]
                    [ img
                        [ class "img-thumbnail"
                        , src <| c.thumbnailUrl item
                        ]
                        []
                    ]
                ]
    in
        div [ class "row" ] << List.map renderItem


{-| Open the modal, selecting the given item.
-}
open : (Msg a -> msg) -> a -> Html.Attribute msg
open m =
    Html.Attributes.map m << openModalOnClick



-- Helper Events


ignoreScroll : Html.Attribute (Msg a)
ignoreScroll =
    onWithOptions "wheel"
        { defaultOptions
            | stopPropagation = True
            , preventDefault = True
        }
    <|
        Decode.succeed Noop


ignoreMove : Html.Attribute (Msg a)
ignoreMove =
    onWithOptions "touchmove"
        { defaultOptions
            | stopPropagation = True
            , preventDefault = True
        }
    <|
        Decode.succeed Noop


nextOnClick : Html.Attribute (Msg a)
nextOnClick =
    onWithOptions "click" { defaultOptions | stopPropagation = True } <|
        Decode.succeed Next


previousOnClick : Html.Attribute (Msg a)
previousOnClick =
    onWithOptions "click" { defaultOptions | stopPropagation = True } <|
        Decode.succeed Previous


closeModalOnEsc : Html.Attribute (Msg a)
closeModalOnEsc =
    onWithOptions "keyup" { defaultOptions | stopPropagation = True } <|
        (keyCode
            |> Decode.andThen
                (\code ->
                    if code == 27 then
                        Decode.succeed Close
                    else
                        Decode.fail "Not ESC"
                )
        )


closeModalOnClick : Html.Attribute (Msg a)
closeModalOnClick =
    onWithOptions "click"
        { defaultOptions | stopPropagation = True }
    <|
        Decode.succeed Close


openModalOnClick : a -> Html.Attribute (Msg a)
openModalOnClick =
    onWithOptions "click"
        { defaultOptions | preventDefault = True }
        << Decode.succeed
        << Select
