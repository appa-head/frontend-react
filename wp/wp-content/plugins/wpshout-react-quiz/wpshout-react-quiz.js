/* JSON data */
var myData = [
  { questionText: "Is introducing React.js into a WordPress environment difficult?", answers: [ "Yes", "No", "Not necessarily" ] },
  { questionText: "Do you want to learn how?", answers: [ "Sure!", "Well, okay", "Goodbye" ] }
];

var answerData = [];

/* React.js Classes */
var Quiz = React.createClass({
  // The Quiz needs state: The sum total of the selected answers from all the QuizQuestions
  getInitialState: function() {
    var quizData = myData;
    var resultData = {};
    return {
      quizData: myData,
      resultData: answerData
     };
  },

  handleChildChange: function(childIndex, childValue) {
    answerData[childIndex] = {
      index: childIndex,
      answerSelected: childValue
    };
    this.setState( { resultData: answerData } );
  },

  render: function() {
    var quizQuestions = this.state.quizData.map( function( thisQuizQuestion, thisQuestionNumber ) {

      return (
        <QuizQuestion questionNumber={thisQuestionNumber} question={thisQuizQuestion.questionText} answers={thisQuizQuestion.answers} handleChange={this.handleChildChange} />
      );
    }, this );

    var quizResults = this.state.resultData.map( function( thisResult, thisResultNumber ) {
      return (
        <div>
            <QuizResult number={thisResultNumber + 1} result={thisResult.answerSelected} />
        </div>
      );
    } );

    return (
      <div class="quiz">
          <h2>Questions</h2>
          {quizQuestions}
          <p><hr /></p>
          <h2>Results</h2>
          { quizResults }
      </div>
    );
  }
});


var QuizQuestion = React.createClass({
  // Each QuizQuestion needs state: Which answer is selected

  getInitialState: function() {
    return { selectedOption: '' };
  },

  handleChange: function( changeEvent ) {
    // Set which is checked
    this.setState({
      selectedOption: changeEvent.target.value
    });

    // Pass that information back to the quiz
    this.props.handleChange(changeEvent.target.name, changeEvent.target.value);
  },

  render: function() {
    var quizAnswers = this.props.answers.map( function( thisQuizAnswer, index ) {

      return (
        <span>
          <input type="radio" name={ this.props.questionNumber } value={thisQuizAnswer}  onChange={ this.handleChange } checked={ this.state.selectedOption === thisQuizAnswer } ></input>
          {thisQuizAnswer}
          <br />
        </span>
      );
    }.bind(this) );

    return (
      <div class="question">
        <h3>{this.props.question}</h3>
        <fieldset>
          {quizAnswers}
        </fieldset>
      </div>
    );
  }
});

var QuizResult = React.createClass({
  render: function() {
    return (
      <div class="results">
          <h3>Question {this.props.number}</h3>
          You answered <em>{this.props.result}</em>.
      </div>
    );
  }

});

ReactDOM.render(<Quiz />, document.getElementById('quiz'));